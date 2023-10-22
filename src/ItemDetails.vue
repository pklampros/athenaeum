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
				<h3 v-if="item.contributorData.type === 'text'"> {{ item.contributorData.text }} </h3>
				<div v-else-if="item.contributorData.contributors"
					 style="display: flex; height: 44px; align-items: center;"
				     @mouseenter="visible.cbButton = true"
					 @mouseleave="visible.cbButton = false">
					<span v-for="(contributor, index) in item.contributorData.contributors"
								:key="contributor" style="display: flex;">
						<span v-if="index !== 0">,&nbsp;</span>
						<span v-if="contributor.displayName.includes('…')">…</span> 
						<NcUserBubble v-else :margin="4" :size="30"
								  :display-name="contributor.displayName">
							<span style="padding: 4px 10px; border-radius: 5px;">
								{{ contributor.firstName + contributor.name }}
							</span>
						</NcUserBubble>
					</span>
					<div v-show="visible.cbButton">
						<NcButton
							aria-label="Edit authors"
							@click="editing.contributors = !editing.contributors">
							<template #icon>
								<Pencil :size="18" />
							</template>
						</NcButton>
					</div>
				</div>
				<!-- Enabling but hiding this so that the processing works -->
				<div v-show="editing.contributors"
					 style="padding:0px 10px; border-radius: 16px; border: 2px solid var(--color-border);">
					<AuthorEditList
						@interface="setAuthorListInterface"
						@authorListUpdated="authorListUpdated"/>
				</div>
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
import NcUserBubble from '@nextcloud/vue/dist/Components/NcUserBubble'

import Delete from 'vue-material-design-icons/Delete.vue';
import School from 'vue-material-design-icons/School.vue';
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue';
import Pencil from 'vue-material-design-icons/Pencil.vue'

import AuthorEditList from './AuthorEditList.vue'

import { showError } from '@nextcloud/dialogs'
import { fetchItemDetails, convertToLibraryItemDetailed, itemChangeFolder } from './service/ItemService'

export default {
	name: 'ItemDetails',
	components: {
		// components
		NcAppContentDetails,
		NcRichContenteditable,
		NcEmptyContent,
		NcRichText,
		NcButton,
		NcUserBubble,

		// icons
		Delete,
		School,
		OpenInNew,
		Pencil,

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
			fixes: [],
			visible: {
				'cbButton': false,
			},
			editing: {
				'contributors': false,
			},
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

			if (item.contributorData.type == "text") {
				this.$options.authorListInterface.setAuthorListFromText(item.contributorData.text);
			} else {
				this.$options.authorListInterface.setAuthorList(item.contributorData.contributors);
			}
		},
	},
	methods: {
		// Setting the interface when emitted from child
		setAuthorListInterface(authorListInterface) {
			this.$options.authorListInterface = authorListInterface;
			if (this.item.contributorData.type == "text") {
				this.$options.authorListInterface.setAuthorListFromText(this.item.contributorData.text);
			} else {
				this.$options.authorListInterface.setAuthorList(this.item.contributorData.contributors);
			}
		},
		authorListUpdated(newAuthorList) {
			this.item.contributorData.contributors = newAuthorList;
			this.item.contributorData.type = "list";
		},
		itemChangedFolder() {
			this.item = null;
			//window.location = '/apps/athenaeum/items/' + this.$route.params.folder;
		},
		addToLibrary() {
			let detailedItem = this.item;
			detailedItem.authorList = this.item.contributorData.contributors;
			convertToLibraryItemDetailed(detailedItem);
			this.itemChangedFolder();
		},
		decideLater() {
			itemChangeFolder(this.item.id, "inbox:decide_later");
			this.itemChangedFolder();
		},
		markItemDeleted() {
			itemChangeFolder(this.item.id, "wastebasket");
			this.itemChangedFolder();
		},
		hasEllipsis(text) {
			return text.includes('…') || text.includes("...")
		},
		extractSourceData(unmappedSourceData) {
			let sourceData = [];
			for (let sourceInfo of unmappedSourceData) {
				let newSource = {};
				console.log(sourceInfo.extra)
				for (let [key, value] of Object.entries(sourceInfo.extra)) {
					if (key == 'journal') {
						newSource['journal'] = value;
					} else if (key == 'authors') {
						newSource['authors'] = value;
					}
				}
				sourceData.push(newSource);
			}
			return sourceData;
		},
		extractFieldData(unmappedFieldData) {
			let itemFieldData = {};
			for (let fieldData of unmappedFieldData) {
				let fieldName = fieldData['name']; 
				if (fieldName == 'url') {
					itemFieldData['url'] = fieldData['value'];
				} else if (fieldName == 'journal') {
					itemFieldData['journal'] = fieldData['value'];
				} else if (fieldName == 'authors') {
					itemFieldData['authors'] = fieldData['value'];
				}
			}
			return itemFieldData;
		},
		getContributorData(itemDetails, itemFieldData, sourceData) {
			let contributorData = {
				type: "list"
			};
			if (itemDetails.contributions) {
				contributorData.contributors = [];
				// contributor
				for (let contributor of itemDetails.contributions) {
					contributorData.contributors.push({
						'name': contributor['last_name'],
						'firstName': contributor['first_name'],
						'displayName': contributor['contributor_name_display']
					});
				}
			}
			if (!contributorData.contributors || contributorData.contributors.length === 0) {
				// not found as contributors, look elsewhere
				contributorData.type = "text";
				contributorData.text = "";
				if (itemFieldData['authors']) {
					// found as a field
					contributorData.text = itemFieldData['authors']
				} else {
					// look into the sources
					for (let source of sourceData) {
						if (source['authors']) {
							contributorData.text = source['authors']
						}
					}
				}
			}
			return contributorData;
		},
		async getItem(itemId) {
			try {
				let itemDetails = await fetchItemDetails(itemId);
				console.log("ItemDetails", itemDetails);
				let itemFieldData = this.extractFieldData(itemDetails.fieldData);
				let sourceData = this.extractSourceData(itemDetails.sourceInfo);
				let contributorData = this.getContributorData(itemDetails,
					itemFieldData, sourceData);
				// go over the inbox-specific fieldData?
				
				let item = itemDetails.item;
				item.id = itemId;
				item.journal = "";
				if (itemFieldData['journal']) {
					// found as a field
					item.journal = itemFieldData['journal']
				} else {
					// look into the sources
					for (let source of sourceData) {
						if (source['journal']) {
							item.journal = source['journal']
						}
					}
				}
				if (itemFieldData['url']) {
					item.url = itemFieldData['url']
				}
				
				item.sourceInfo = itemDetails.sourceInfo;
				item.sourceInfo.sort(function(a, b) {
					return parseFloat(a.importance) - parseFloat(b.importance);
				});
				item.contributorData = contributorData;
				console.log("item", item);
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