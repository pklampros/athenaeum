<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcAppContentDetails v-if="item">
		<div style="max-width: 900px; margin: 0 auto;">
			<div style="position: sticky; padding: 30px 18px;">
				<h2
					:title="item.title"
					style="display: flex; align-items: center; justify-content: space-between;">
					{{ item.title }}
					<a :href="item.url" target="_blank"><OpenInNew></a>
				</h2>
				<h3> {{ item.journal }} </h3>
				<h3> {{ authorListDisplay }} </h3>
				&nbsp;
				<h3 style="font-weight: bold;">Excerpts:</h3>
				<ul style="list-style: inherit; padding: 4px 0 4px 44px;">
					<li v-for="sourceInfoPoint in item.sourceInfo"
								:key="sourceInfoPoint">
						<div>
							<span style="color: var(--color-main-text);font-weight: bold;">
								{{ sourceInfoPoint.extra.excerpt }}
							</span>
							<div style="padding: 5px 0px;">
								<span style="color: var(--color-text-maxcontrast);">
									Search term: {{ sourceInfoPoint.extra.searchTerm }}
								</span>
							</div>
						</div>
					</li>
				</ul>
			</div>
			<div style="padding:0px 10px; border-radius: 16px; border: 2px solid var(--color-border);">
				<div class="field-label">
					<h3>Title</h3>
				</div>
				<NcRichContenteditable
					placeholder="Title"
					:error="hasEllipsis(item.title)"
					:value.sync="item.title" />
				<div class="field-label">
					<h3>URL</h3>
				</div>
				<NcRichContenteditable
					placeholder="URL"
					:value.sync="item.url" />
				<div class="field-label">
					<h3>Journal</h3>
				</div>
				<NcRichContenteditable
					placeholder="Journal"
					:error="hasEllipsis(item.journal)"
					:value.sync="item.journal"/>
				<AuthorEditList
					@interface="setAuthorListInterface"
					@authorListUpdated="authorListUpdated"/>
				&nbsp;
			</div>
			<div style="display: flex; justify-content: right; align-items: center; padding: 16px;">
				<NcButton
					ariaLabel="Remove item"
					:disabled="!this.item.title || !this.item.url"
					@click="markItemDeleted"
					type="primary">
					<template #icon>
						<Delete :size="20" />
					</template>
				</NcButton>
				&nbsp;
				<NcButton
					:disabled="!this.item.title || !this.item.url"
					@click="decideLater"
					type="primary">
					Decide later
				</NcButton>
				&nbsp;
				<NcButton
					:disabled="!this.item.title || !this.item.url"
					@click="addToLibrary"
					type="primary">
					Add to Library
				</NcButton>
			</div>
		</div>
	</NcAppContentDetails>
	<NcAppContentDetails v-else>
		<NcEmptyContent
			:title="t('athenaeum', 'No item selected')">
			<template #icon>
				<School :size="65" />
			</template>
		</NcEmptyContent>
	</NcAppContentDetails>
</template>

<script>
import NcAppContentDetails from '@nextcloud/vue/dist/Components/NcAppContentDetails'
import NcRichContenteditable from '@nextcloud/vue/dist/Components/NcRichContenteditable'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent'
import NcRichText from '@nextcloud/vue/dist/Components/NcRichText'
import NcButton from '@nextcloud/vue/dist/Components/NcButton'

import Delete from 'vue-material-design-icons/Delete.vue';
import School from 'vue-material-design-icons/School.vue';
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue';

import AuthorEditList from './AuthorEditList.vue'

import { showError } from '@nextcloud/dialogs'
import { fetchItemDetails } from './service/ItemService'
// import { convertToLibraryItemDetailed } from './service/LibraryItemService'

export default {
	name: 'ItemDetails',
	components: {
		// components
		NcAppContentDetails,
		NcRichContenteditable,
		NcEmptyContent,
		NcRichText,
		NcButton,

		// icons
		Delete,
		School,
		OpenInNew,

		// project components
		AuthorEditList
	},
	props: {
		itemId: {
			type: Number,
			required: true,
		},
	},
	data() {
		return {
			item: null,
			authorListDisplay: null,
			authorList: [],
		}
	},
	async mounted() {
		this.loading = true;
		if (this.itemId) {
			// this is required to trigger the update the various details when
			// the itemId is first given (for example though the route)
			this.updateDetails(this.itemId);
		}
		this.loading = false
	},
	watch: {
		itemId: async function (itemId) {
			// this is required to trigger the update of the various details when
			// itemId is updated (does not work the first time i.e. through the route)
			this.updateDetails(itemId);
		},
		item: async function (item) {
			if (!item || !this.$options || !this.$options.authorListInterface) return;
			this.$options.authorListInterface.setAuthorListFromText(item.authors);
		},
	},
	methods: {
		// Setting the interface when emitted from child
		setAuthorListInterface(authorListInterface) {
			this.$options.authorListInterface = authorListInterface;
			this.$options.authorListInterface.setAuthorListFromText(this.item.authors)
		},
		authorListUpdated(newAuthorList) {
			this.authorList = newAuthorList;
			this.authorListDisplay = newAuthorList.map(author => author.displayName).join(', ');
		},
		addToLibrary() {
			let detailedItem = this.item;
			detailedItem.authorList = this.authorList;
			// convertToLibraryItemDetailed(detailedItem);
		},
		decideLater() {
			itemDecideLater(this.item.id);
		},
		markItemDeleted() {
			this.item = null
		},
		hasEllipsis(text) {
			return text.includes('…') || text.includes("...")
		},
		async getItem(itemId) {
			try {
				let itemDetails = await fetchItemDetails(itemId);
				console.log(itemDetails);
				let item = itemDetails.item;
				// go over the inbox-specific fieldData?
				for (let fieldData of itemDetails.fieldData) {
					let fieldName = fieldData['name']; 
					if (fieldName == 'url') {
						item['url'] = fieldData['value'];
					} else if (fieldName == 'journal') {
						item['journal'] = fieldData['value'];
					} else if (fieldName == 'authors') {
						item['authors'] = fieldData['value'];
					}
				}
				for (let sourceInfo of itemDetails.sourceInfo) {
					console.log(sourceInfo.extra)
					for (let [key, value] of Object.entries(sourceInfo.extra)) {
						if (key == 'journal') {
							item['journal'] = value;
						} else if (key == 'authors') {
							item['authors'] = value;
						}
					}
				}
				item.id = itemId;
				if (!item.journal) item.journal = "";
				if (!item.authors) item.authors = "";
				item.sourceInfo = itemDetails.sourceInfo;
				item.sourceInfo.sort(function(a, b) {
					return parseFloat(a.importance) - parseFloat(b.importance);
				});
				console.log(item);
				return item;
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch item details (route mounting failed)'))
			}
			return null;
		},
		async updateDetails(itemId) {
			if (!itemId) return;
			this.item = await this.getItem(itemId);
			this.authorListDisplay = this.item.authors;
		},
	},
}

</script>


<style lang="scss" scoped>

	.input-field {
		margin: 8px 0px;
	}

	.rich-contenteditable__input {
		text-align: initial;
		&[error="true"] {
			border-color: var(--color-error) !important;
		}
	}

	.rich-contenteditable__input--empty:before {
		position: inherit;
	}

	:deep(.field-label) {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 10px 1px 0px 0px;
	}

	:deep(.field-label h3) {
		font-weight: bold;
		margin: 8px 0px 8px 12px;
		text-align: start;
	}
</style>