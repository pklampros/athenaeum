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
					<a :href="source.url"
						target="_blank" rel="noopener noreferrer">
						<OpenInNew />
					</a>
				</h2>
				<h3> {{ source.description }} </h3>
				<h3> UID: {{ source.uid }} </h3>
				<h3> Type: {{ source.sourceType }} </h3>
				<h3> Importance: {{ source.importance }} </h3>
			</div>
			<div class="details-group" v-if="source">
				<span id="item-title-label"
					class="field-label">
					{{ t('athenaeum', 'Title') }}
				</span>
				<NcRichContenteditable
					aria-labelledby="item-title-label"
					placeholder="Title"
					v-model="source.title" />
				<span id="item-description-label"
					class="field-label">
					{{ t('athenaeum', 'Description') }}
				</span>
				<NcRichContenteditable placeholder="Description"
					aria-labelledby="item-description-label"
					v-model="source.description"/>
				<span id="item-importance-label"
					title="This is useful for sorting items in the inbox"
					class="field-label">
					{{ t('athenaeum', 'Importance') }}
				</span>
				<NcInputField placeholder="Importance"
					aria-labelledby="item-importance-label"
					type="number"
					v-model="source.importance"/>
				&nbsp;
			</div>
			<div class="save-row">
				<NcButton aria-label="Remove source"
					variant="primary"
					@click="markSourceDeleted">
					<template #icon>
						<Delete :size="20" />
					</template>
				</NcButton>
				&nbsp;
				<NcButton :disabled="!dataModified"
					variant="primary"
					@click="saveChanges">
					Save
				</NcButton>
			</div>
		</div>
	</NcAppContentDetails>
	<NcAppContentDetails v-else>
		<NcEmptyContent :name="t('athenaeum', 'No source selected')">
			<template #icon>
				<School :size="65" />
			</template>
		</NcEmptyContent>
	</NcAppContentDetails>
</template>

<script>
import { NcAppContentDetails, NcTextField, NcInputField, NcRichContenteditable, NcEmptyContent, NcButton } from '@nextcloud/vue'

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
		source: {
			handler() {
				if (this._suppressWatcher) return
				this.dataModified = true
			},
			deep: true,
		},
		'source.id'() {
			this._suppressWatcher = true
			this.dataModified = false
			this.$nextTick(() => { this._suppressWatcher = false })
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

.details-group {
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
</style>
