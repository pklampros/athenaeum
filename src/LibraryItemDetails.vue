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
				<h3> Authors: {{ authorListDisplay }} </h3>
				<h3> Editors: {{ editorListDisplay }} </h3>
				<h3> Other contributions: {{ otherContributionsDisplay }} </h3>
				&nbsp;
				<!-- <h3 style="font-weight: bold;">Excerpts:</h3>
				<ul style="list-style: inherit; padding: 4px 0 4px 44px;">
					<li v-for="alertExcerpt in alertExcerpts"
								:key="alertExcerpt">
						<div>
							<span style="color: var(--color-main-text);font-weight: bold;">
								{{ alertExcerpt.excerpt }}
							</span>
							<div style="padding: 5px 0px;">
								<span style="color: var(--color-text-maxcontrast);">
									Search term: {{ alertExcerpt.term }}
								</span>
							</div>
						</div>
					</li>
				</ul> -->
			</div>

			Raw:<br /><br />
			Item: <pre>{{ JSON.stringify(this.item, null, 2) }}</pre><br /><br />
			FieldData: <pre>{{ JSON.stringify(this.itemFieldData, null, 2) }}</pre><br /><br />
			Contributions: <pre>{{ JSON.stringify(this.contributions, null, 2) }}</pre><br /><br />

			<div style="padding:0px 10px; border-radius: 16px; border: 2px solid var(--color-border);">
				<div class="field-label">
					<h3>Title</h3>
				</div>
				<NcRichContenteditable
					placeholder="Title"
					:error="hasEllipsis(item.title)"
					:value.sync="item.title" />
					<div v-for="fieldData in itemFieldData"
							:key="fieldData.name">
						
						<div class="field-label">
							<h3>fieldData.name</h3>
						</div>
						<NcRichContenteditable
							placeholder="fieldData.name"
							:error="hasEllipsis(fieldData.value)"
							:value.sync="fieldData.value" />
					</div>
				<!-- <AuthorEditList
					@interface="setAuthorListInterface"
					@authorListUpdated="authorListUpdated"/> -->
				&nbsp;
			</div>
			<div style="display: flex; justify-content: right; align-items: center; padding: 16px;">
				<NcButton
					ariaLabel="Remove item"
					:disabled="!this.item.title || !this.item.url"
					@click="markInboxItemDeleted"
					type="primary">
					<template #icon>
						<Delete :size="20" />
					</template>
				</NcButton>
				&nbsp;
				<NcButton
					:disabled="!this.item.title || !this.item.url"
					@click="saveItem"
					type="primary">
					Save
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
import { fetchLibraryItemDetails } from './service/LibraryItemService'

export default {
	name: 'LibraryItemDetails',
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
	data() {
		return {
			item: null,
			itemFieldData: {},
			alertExcerpts: [],
			authorDisplay: null,
			editorDisplay: null,
			otherContributionsDisplay: null,
			contributions: [],
		}
	},
	computed: {
		itemId() {
			return parseInt(this.$route.params.libraryItemId, 0)
		},
	},
	watch: {
		itemId: async function (itemId) {
			if (!itemId) return;
			try {
				let itemDetails = await fetchLibraryItemDetails(itemId);
				console.log(itemDetails);
				this.item = itemDetails.item;
				this.itemFieldData = itemDetails.fieldData;
				if (!this.item.authors) this.item.authors = "";
				this.authorListDisplay = this.item.authors;
				this.contributions = itemDetails.contributions;
				this.contributions.sort(function(a, b) {
					return parseFloat(a.orde) - parseFloat(b.order);
				});
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch item details (route mounting failed)'))
			}
		},
		item: async function (item) {
			if (!item || !this.$options || !this.$options.authorListInterface) return;
			this.$options.authorListInterface.setAuthorListFromText(this.item.authors);
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
		saveItem() {
			// unimplemented yet
		},
		hasEllipsis(text) {
			return text.includes('…') || text.includes("...")
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