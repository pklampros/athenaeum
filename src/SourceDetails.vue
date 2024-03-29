<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcAppContentDetails v-if="source">
		<div style="max-width: 900px; margin: 0 auto;">
			<div style="position: sticky; padding: 30px 18px;">
				<h2 :title="source.title"
					style="display: flex; align-items: center; justify-content: space-between;">
					{{ source.title }}
					<a :href="source.url" target="_blank"><OpenInNew /></a>
				</h2>
				<h3> {{ source.description }} </h3>
				<h3> UID: {{ source.uid }} </h3>
				<h3> Type: {{ source.sourceType }} </h3>
				<h3> Importance: {{ source.importance }} </h3>
			</div>
			<div style="padding:0px 10px; border-radius: 16px; border: 2px solid var(--color-border);">
				<div class="field-label">
					<h3>Title</h3>
				</div>
				<NcTextField placeholder="Title"
					label-outside="true"
					:value.sync="source.title"
					@update:value="setDataModified()" />
				<div class="field-label">
					<h3>Description</h3>
				</div>
				<NcRichContenteditable placeholder="Description"
					:value.sync="source.description"
					@update:value="setDataModified()" />
				<div class="field-label">
					<h3 title="This is useful for sorting items in the inbox">
						Importance
					</h3>
				</div>
				<NcInputField placeholder="Importance"
					label-outside="true"
					type="number"
					:value.sync="source.importance"
					@update:value="setDataModified()" />
				&nbsp;
			</div>
			<div style="display: flex; justify-content: right; align-items: center; padding: 16px;">
				<NcButton aria-label="Remove source"
					type="primary"
					@click="markSourceDeleted">
					<template #icon>
						<Delete :size="20" />
					</template>
				</NcButton>
				&nbsp;
				<NcButton :disabled="!dataModified"
					type="primary"
					@click="saveChanges">
					Save
				</NcButton>
			</div>
		</div>
	</NcAppContentDetails>
	<NcAppContentDetails v-else>
		<NcEmptyContent :title="t('athenaeum', 'No source selected')">
			<template #icon>
				<School :size="65" />
			</template>
		</NcEmptyContent>
	</NcAppContentDetails>
</template>

<script>
import NcAppContentDetails from '@nextcloud/vue/dist/Components/NcAppContentDetails.js'
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'
import NcInputField from '@nextcloud/vue/dist/Components/NcInputField.js'
import NcRichContenteditable from '@nextcloud/vue/dist/Components/NcRichContenteditable.js'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'

import Delete from 'vue-material-design-icons/Delete.vue'
import School from 'vue-material-design-icons/School.vue'
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue'

import { showError } from '@nextcloud/dialogs'
import { fetchSourceDetails, updateSource } from './service/SourceService.js'

export default {
	name: 'SourceDetails',
	components: {
		// components
		NcAppContentDetails,
		NcTextField,
		NcInputField,
		NcRichContenteditable,
		NcEmptyContent,
		NcButton,

		// icons
		Delete,
		School,
		OpenInNew,
	},
	props: {
		sourceId: {
			type: Number,
			required: true,
		},
	},
	data() {
		return {
			source: null,
			fixes: [],
			visible: {
				cbButton: false,
			},
			dataModified: false,
		}
	},
	watch: {
		async sourceId(sourceId) {
			// this is required to trigger the update of the various details when
			// sourceId is updated (does not work the first time i.e. through the route)
			this.updateDetails(sourceId)
		},
		async source(source) {
			if (!source || !this.$options || !this.$options.authorListInterface) return

			if (source.contributorData.type === 'text') {
				this.$options.authorListInterface.setAuthorListFromText(source.contributorData.text)
			} else {
				this.$options.authorListInterface.setAuthorList(source.contributorData.contributors)
			}
		},
	},
	async mounted() {
		this.loading = true
		if (this.sourceId) {
			// this is required to trigger the update the various details when
			// the sourceId is first given (for example though the route)
			this.updateDetails(this.sourceId)
		}
		this.loading = false
	},
	methods: {
		setDataModified() {
			this.dataModified = true
		},
		async getSource(sourceId) {
			try {
				const sourceDetails = await fetchSourceDetails(sourceId)
				return sourceDetails
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch source details (route mounting failed)'))
			}
			return null
		},
		async saveChanges() {
			try {
				const response = await updateSource(
					this.source.id, this.source.importance,
					this.source.title, this.source.description,
				)
				return response
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch source details (route mounting failed)'))
			}
			return null
		},
		async updateDetails(sourceId) {
			if (!sourceId) return
			this.source = await this.getSource(sourceId)
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
		&[error='true'] {
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
