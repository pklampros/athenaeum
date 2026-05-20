<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcAppContentDetails v-if="item && itemSummary?.id >= 0">
		<div style="max-width: 900px; margin: 0 auto;">
			<div style="position: sticky; padding: 30px 18px;">
				<div style="display:flex; flex-direction: column">
					<h2 :title="item.title"
						:class="{ toedit: visible.itemEditButton }"
						style="display: flex; align-items: center; justify-content: space-between;">
						<NcHighlight :text="item.title"
							:search="queryText" />
						<a :href="item.url"
							target="_blank" rel="noopener noreferrer">
							<OpenInNew />
						</a>
					</h2>
					<div style="display:flex; flex-direction: row;">
						<h3 :class="{ toedit: visible.itemEditButton }"
							style="flex-grow: 1;">
							{{ item.journal }}
						</h3>
						<div style="align-content: center;">
							<NcButton aria-label="Edit item data"
								size="small"
								:type="editButtonType(visible.itemEditButton)"
								@click="editing.item = !editing.item"
								@mouseover="visible.itemEditButton = true"
								@mouseleave="visible.itemEditButton = false">
								<template #icon>
									<Pencil :size="18" />
								</template>
							</NcButton>
						</div>
					</div>

					<div v-show="editing.item"
						class="details-group"
						v-if="item">
						<span id="item-title-label"
							class="field-label">
							{{ t('athenaeum', 'Title') }}
						</span>
						<NcRichContenteditable
						 	aria-labelledby="item-title-label"
							placeholder="Title"
							:error="hasEllipsis(item.title)"
							v-model="item.title"/>
						<span id="item-url-label"
							class="field-label">
							{{ t('athenaeum', 'URL') }}
						</span>
						<div class="url-row">
							<NcRichContenteditable
						 		aria-labelledby="item-url-label"
								placeholder="URL"
								v-model="item.url"/>
							<NcButton aria-label="Add"
								:name="t('athenaeum', 'Attach file from URL')"
								@click="attachFromUrl">
								<template #icon>
									<PlusCircle :size="25" />
								</template>
							</NcButton>
						</div>
						<span id="item-journal-label"
							class="field-label">
							{{ t('athenaeum', 'Journal') }}
						</span>
						<NcRichContenteditable
						 	aria-labelledby="item-journal-label"
							placeholder="Journal"
							:error="hasEllipsis(item.journal)"
							v-model="item.journal"/>
						<div class="save-row">
							<NcButton :disabled="!dataModified"
								variant="primary"
								@click="saveChanges">
								Save
							</NcButton>
						</div>
					</div>
				</div>

				<div v-if="item.contributorData?.contributors?.length"
					style="display: flex; height: 44px; align-items: center; flex-wrap: wrap;">
					<span v-for="(contributor, index) in item.contributorData.contributors"
						:key="contributor.id ?? index"
						style="display: flex;">
						<span v-if="index !== 0">,&nbsp;</span>
						<span v-if="contributor.displayName.includes('…')">…</span>
						<NcUserBubble v-else
							:margin="4"
							:size="30"
							:primary="visible.cbButton"
							:display-name="contributor.displayName">
							<span style="padding: 4px 10px; border-radius: 5px;">
								{{ contributor.firstName + contributor.name }}
							</span>
						</NcUserBubble>
					</span>
					<div style="margin-left:auto">
						<NcButton aria-label="Edit authors"
							size="small"
							:type="editButtonType(visible.cbButton)"
							@click="editing.contributors = !editing.contributors"
							@mouseover="visible.cbButton = true"
							@mouseleave="visible.cbButton = false">
							<template #icon>
								<Pencil />
							</template>
						</NcButton>
					</div>
				</div>
				<h3 v-else-if="item.contributorData?.type === 'text'">
					{{ item.contributorData.text }}
				</h3>
				<!-- Enabling but hiding this so that the processing works -->
				<div v-show="editing.contributors"
					style="padding:0px 10px; border-radius: 16px; border: 2px solid var(--color-border);">
					<AuthorEditList :contributor-data="item.contributorData"
                		@update:contributor-data="onContributorDataUpdated" />
				</div>

				<h3 style="font-weight: bold;">
					Excerpts:
				</h3>
				<ul style="list-style: inherit; padding: 4px 0 4px 44px;">
					<li v-for="sourceInfoPoint in item.sourceInfo"
						:key="sourceInfoPoint.id ?? sourceInfoPoint.extra_item_data.excerpt">
						<div>
							<span style="color: var(--color-main-text);font-weight: bold;">
								<NcHighlight :text="sourceInfoPoint.extra_item_data.excerpt"
									:search="queryText" />
							</span>
							<div style="padding: 5px 0px;">
								<span style="color: var(--color-text-maxcontrast);">
									Search term: {{ sourceInfoPoint.extra_source_data.searchTerm }}
								</span>
							</div>
							<div style="padding: 5px 0px;">
								<span style="color: var(--color-text-maxcontrast);">
									Received: {{
										new Date(Date.parse(
											sourceInfoPoint.extra_source_data.emailReceived.date
										)).toLocaleDateString(undefined,
											{
												year: "numeric",
												month: "short",
												day: "numeric"
											})
									}}
								</span>
							</div>
						</div>
					</li>
				</ul>
			</div>

			<div class="details-group"
				style="margin-top: 10px;">
				<div class="field-label">
					<h3>Attachments ({{ (item && item.attachments) ? item.attachments.length : 0 }})</h3>
					<div class="list-plus-button-wrap">
						<NcButton aria-label="Add"
							variant="tertiary"
							@click="showAttachmentModal">
							<template #icon>
								<PlusCircle :size="20" />
							</template>
						</NcButton>
					</div>
				</div>
				<ul v-if="item && item.attachments && item.attachments.length">
					<NcListItem v-for="attachment in item.attachments"
						:key="attachment.itemAttachment.id"
						:name="attachment.itemAttachment.path"
						compact
						:force-display-actions="true">
						<template #extra-actions>
							<NcButton v-if="canOpenAttachment(attachment)"
								:name="t('athenaeum', 'Open attachment')"
								:aria-label="t('athenaeum', 'Open attachment')"
								variant="tertiary"
								:href="attachment.openPath"
								target="_blank" rel="noopener noreferrer">
								<template #icon>
									<OpenInApp :size="20" />
								</template>
							</NcButton>
							<NcButton :name="t('athenaeum', 'Download attachment')"
								aria-label="Download attachment"
								variant="tertiary"
								:href="attachment.downloadPath"
								download>
								<template #icon>
									<DownloadCircle :size="20" />
								</template>
							</NcButton>
							<NcButton :name="t('athenaeum', 'Remove attachment')"
								aria-label="Remove attachment"
								variant="tertiary"
								@click="removeAttachment(attachment.itemAttachment.id)">
								<template #icon>
									<MinusCircle :size="20" />
								</template>
							</NcButton>
						</template>
					</NcListItem>
				</ul>
			</div>
			<div class="details-footer">
				<NcButton aria-label="Remove item"
					:disabled="!item.title || !item.url"
					variant="primary"
					@click="markItemDeleted">
					<template #icon>
						<Delete :size="20" />
					</template>
				</NcButton>
				&nbsp;
				<NcButton :disabled="!item.title || !item.url"
					variant="primary"
					@click="decideLater">
					Decide later
				</NcButton>
				&nbsp;
				<NcButton :disabled="!item.title || !item.url"
					variant="primary"
					@click="addToLibrary">
					Add to Library
				</NcButton>
			</div>
		</div>
		<AttachmentUploadModal v-model:visible="attachmentModalVisible"
			v-model:item-id="item.id"
			@modal-closed="hideAttachmentModal" />
		<ApproveDialog v-model:visible="hasAttachmentRemoveId"
			name="Confirmation"
			message="Are you sure you want to remove this attachment?"
			@dialog-ok="okToRemoveAttachment" />
	</NcAppContentDetails>
	<NcAppContentDetails v-else>
		<NcEmptyContent :name="t('athenaeum', 'No item selected')">
			<template #icon>
				<School :size="65" />
			</template>
		</NcEmptyContent>
	</NcAppContentDetails>
</template>

<script>
import {
	NcAppContentDetails,
	NcRichContenteditable,
	NcEmptyContent,
	NcButton,
	NcUserBubble,
	NcListItem,
	NcHighlight,
} from '@nextcloud/vue'

import Delete from 'vue-material-design-icons/Delete.vue'
import School from 'vue-material-design-icons/School.vue'
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import PlusCircle from 'vue-material-design-icons/PlusCircle.vue'
import MinusCircle from 'vue-material-design-icons/MinusCircle.vue'
import DownloadCircle from 'vue-material-design-icons/DownloadCircle.vue'
import OpenInApp from 'vue-material-design-icons/OpenInApp.vue'

import AuthorEditList from './AuthorEditList.vue'

import { showError } from '@nextcloud/dialogs'
import {
	fetchItemDetails,
	scholarToFull,
	fetchItemAttachments,
	removeItemAttachment,
	attachFromUrl,
	updateItem,
} from './service/ItemService.js'

import { authorMxn } from './mixins/authors.js'

import AttachmentUploadModal from './AttachmentUploadModal.vue'
import ApproveDialog from './ApproveDialog.vue'

import 'toastify-js/src/toastify.css'

import Toastify from 'toastify-js'

export default {
	name: 'ItemDetails',
	components: {
		// components
		NcAppContentDetails,
		NcRichContenteditable,
		NcEmptyContent,
		NcButton,
		NcUserBubble,
		NcListItem,
		NcHighlight,

		// icons
		Delete,
		School,
		OpenInNew,
		Pencil,
		PlusCircle,
		MinusCircle,
		DownloadCircle,
		OpenInApp,

		// project components
		AuthorEditList,
		AttachmentUploadModal,
		ApproveDialog,
	},
	props: {
		itemSummary: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			item: null,
			fixes: [],
			visible: {
				cbButton: false,
				itemEditButton: false,
			},
			dataModified: false,
			editing: {
				contributors: false,
				item: false,
			},
			attachmentModalVisible: false,
			attachmentRemoveId: null,
			_fetchId: 0,
		}
	},
	computed: {
		hasAttachmentRemoveId() {
			return this.attachmentRemoveId !== null
		},
		queryText() {
			return this.$route.query.lq
				? this.$route.query.lq
				: ''
		},
	},
	watch: {
		async itemSummary(itemSummary) {
			this.reset()
			// this is required to trigger the update of the various details when
			// itemSummary is updated (does not work the first time i.e. through the route)
			this.fetchDetails(itemSummary)
		},
		item: {
			handler(newItem, oldItem) {
				// Detect real user edits (not loads or switches)
				if (this._itemLoading) return
				if (!oldItem || !newItem) return
				if (oldItem.id !== newItem.id) return
				this.dataModified = true
			},
			deep: true,
			immediate: true,
		},
		// When a new item arrives, suppress the watcher until it settles
		'item.id'() {
			this._itemLoading = true
			this.dataModified = false
			this.$nextTick(() => { this._itemLoading = false })
		},
	},
	async mounted() {
		this.loading = true
		if (this.itemSummary) {
			// this is required to trigger the update the various details when
			// the itemSummary is first given (for example though the route)
			this.fetchDetails(this.itemSummary)
		}
		this.loading = false
	},
	methods: {
		reset() {
			this.editing.contributors = false
			this.editing.item = false
		},
		async saveChanges() {
			try {
				const response = await updateItem(
					this.item.id, this.item.title,
					this.item.url, this.item.journal,
				)
				return response
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch source details (route mounting failed)'))
			}
			return null
		},
		editButtonType(changeOn) {
			return changeOn ? 'primary' : 'tertiary-no-background'
		},
		onContributorDataUpdated(newData) {
			this.item.contributorData = newData
		},
		async addToLibrary() {
			const detailedItem = this.item
			detailedItem.authorList = this.item.contributorData.contributors
			await scholarToFull(detailedItem)
			this.$emit('item-change-folder', this.item.id, 'library',
				'Item moved to Library')
		},
		decideLater() {
			this.$emit('item-change-folder', this.item.id, 'inbox:decide_later',
				'Item moved to Decide later')
		},
		markItemDeleted() {
			this.$emit('item-change-folder', this.item.id, 'wastebasket',
				'Item moved to Wastebasket')
		},
		hasEllipsis(text) {
			return text.includes('…') || text.includes('...')
		},
		extractSourceData(unmappedSourceData) {
			const sourceData = []
			for (const sourceInfo of unmappedSourceData) {
				const newSource = {}
				for (const [key, value] of Object.entries(sourceInfo.extra_item_data)) {
					if (key === 'journal') {
						newSource.journal = value
					} else if (key === 'authors') {
						newSource.authors = value
					}
				}
				sourceData.push(newSource)
			}
			return sourceData
		},
		extractFieldData(unmappedFieldData) {
			const itemFieldData = {}
			for (const fieldData of unmappedFieldData) {
				const fieldName = fieldData.name
				if (fieldName === 'url') {
					itemFieldData.url = fieldData.value
				} else if (fieldName === 'journal') {
					itemFieldData.journal = fieldData.value
				} else if (fieldName === 'authors') {
					itemFieldData.authors = fieldData.value
				}
			}
			return itemFieldData
		},
		getContributorData(itemDetails, itemFieldData, sourceData) {
			const contributorData = { type: 'list' }
			if (itemDetails.contributions) {
				contributorData.contributors = []
				for (const contributor of itemDetails.contributions) {
					contributorData.contributors.push({
						name: contributor.last_name,
						firstName: contributor.first_name,
						displayName: contributor.contributor_name_display,
					})
				}
			}
			if (!contributorData.contributors || contributorData.contributors.length === 0) {
				// No backend contributions — try to parse from text sources
				let authorsText = ''
				if (itemFieldData.authors) {
					authorsText = itemFieldData.authors
				} else {
					for (const source of sourceData) {
						if (source.authors) {
							authorsText = source.authors
						}
					}
				}
				if (authorsText) {
					// Use the mixin's text parser
					const parsed = authorMxn.getContributorListFromTxt(authorsText)
					if (parsed && parsed.length > 0) {
						contributorData.contributors = parsed
					} else {
						// Couldn't parse — fall back to text mode
						delete contributorData.contributors
						contributorData.type = 'text'
						contributorData.text = authorsText
					}
				} else {
					// No text either — empty
					delete contributorData.contributors
					contributorData.type = 'text'
					contributorData.text = ''
				}
			}
			return contributorData
		},
		async getItem(itemId) {
			try {
				const itemDetails = await fetchItemDetails(itemId)
				const itemFieldData = this.extractFieldData(itemDetails.fieldData)
				const sourceData = this.extractSourceData(itemDetails.sourceInfo)
				const contributorData = this.getContributorData(itemDetails,
					itemFieldData, sourceData)
				const attachmentData = itemDetails.attachments
				// go over the inbox-specific fieldData?

				const item = itemDetails.item
				item.id = itemId
				item.journal = ''
				if (itemFieldData.journal) {
					// found as a field
					item.journal = itemFieldData.journal
				} else {
					// look into the sources
					for (const source of sourceData) {
						if (source.journal) {
							item.journal = source.journal
						}
					}
				}
				if (itemFieldData.url) {
					item.url = itemFieldData.url
				}

				item.sourceInfo = itemDetails.sourceInfo
				item.sourceInfo.sort(function(a, b) {
					return parseFloat(a.importance) - parseFloat(b.importance)
				})
				item.contributorData = contributorData
				item.attachments = attachmentData
				return item
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch item details (route mounting failed)'))
			}
			return null
		},
		async fetchDetails(itemSummary) {
			if (!itemSummary || !itemSummary.id) return
			if (this.item && itemSummary.id === this.item.id) return

			const fetchId = ++this._fetchId
			this.item = itemSummary.title ? {
				id: itemSummary.id,
				title: itemSummary.title,
				url: '',
				journal: '',
				contributorData: { type: 'text', text: '' },
				attachments: [],
			} : null

			const itemFull = await this.getItem(itemSummary.id)
			if (fetchId !== this._fetchId) return  // a newer fetch took over
			this.item = itemFull
		},
		async attachFromUrl() {
			await attachFromUrl(this.item.id, this.item.url)
				.then(async () => {
					this.showToast('New attachment created', 3000)
					fetchItemAttachments(this.item.id).then((attachments) => {
						this.item.attachments = attachments
					})
				})
				.catch(error => {
					this.showToast('Could not fetch URL: ' + error.message, 3000)
				})
		},
		showAttachmentModal() {
			this.attachmentModalVisible = true
		},
		hideAttachmentModal() {
			this.attachmentModalVisible = false
			fetchItemAttachments(this.item.id).then((attachments) => {
				this.item.attachments = attachments
			})
		},
		canOpenAttachment(attachment) {
			return true
			// return attachment.itemAttachment.mimeType === 'application/pdf'
		},
		removeAttachment(attachmentId) {
			this.attachmentRemoveId = attachmentId
		},
		okToRemoveAttachment() {
			if (!this.hasAttachmentRemoveId) return
			const attachmentId = this.attachmentRemoveId
			this.attachmentRemoveId = null
			removeItemAttachment(attachmentId).then(() => {
				fetchItemAttachments(this.item.id).then((attachments) => {
					this.item.attachments = attachments
					this.showToast('Attachment deleted')
				})
			})
		},
		showToast(message, duration = 1500) {
			Toastify({
				text: message,
				duration,
				close: false,
				gravity: 'bottom',
				position: 'center',
				stopOnFocus: true,
				style: {
					background: '#00000066',
					text: 'white',
				},
			}).showToast()
		},
	},
}

</script>

<style lang="scss" scoped>
.details-footer {
	display: flex;
	justify-content: right;
	align-items: center;
	padding: 16px 16px 4px 16px;
	position: sticky;
	bottom: 0;
	background-image: linear-gradient(to top, var(--gradient-main-background));
}

.input-field {
	margin: 8px 0px;
}

.rich-contenteditable__input {
	text-align: initial;

	&[error='true'] {
		border-color: var(--color-error) !important;
	}
}

.rich-contenteditable__input--empty:before {
	position: inherit;
}

.details-group {
	border-radius: 16px;
	border: 2px solid var(--color-border);
    display: flex;
    flex-direction: column;
    gap: calc(var(--default-grid-baseline) * 2);
    padding: calc(var(--default-grid-baseline) * 3);
}

.field-label {
	display: flex;
	justify-content: space-between;
	align-items: center;
	font-weight: bold;
	font-size: 1.17em;
	text-align: start;
}

.field-label + .rich-contenteditable,
.field-label + .url-row {
    margin-top: calc(var(--default-grid-baseline) * 0.5);
}

.save-row {
    display: flex;
    flex-direction: row-reverse;
    margin-top: calc(var(--default-grid-baseline) * 4);
}

.url-row {
    display: flex;
    gap: var(--default-grid-baseline);
    align-items: flex-start;
}
.url-row__input { flex-grow: 1; }


.list-plus-button-wrap {
	// This is the .list-item__wrapper padding (4px), along with
	// the list-item padding (2xbaseline) plus the margin of the
	// actions (1xbaseline) minus the padding of the .field-label.
	// Assumes that #extra-actions are used for the buttons.
	padding: 0 calc(4px + 3 * var(--default-grid-baseline) - 1px) 0 0;
}

.toedit {
	color: var(--color-primary-element-hover);
}
</style>
