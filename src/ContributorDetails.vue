<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcAppContentDetails v-if="contributor">
		<div style="max-width: 900px; margin: 0 auto;">
			<div style="position: sticky; padding: 30px 18px;">
				<h2 :title="getName(contributor)"
					style="display: flex; align-items: center; justify-content: space-between;">
					{{ getName(contributor) }}
				</h2>
			</div>
			<div style="display: flex; justify-content: right; align-items: center; padding: 16px;">
				<NcButton aria-label="Remove contributor"
					type="primary"
					@click="markContributorDeleted">
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
		<NcEmptyContent :title="t('athenaeum', 'No contributor selected')">
			<template #icon>
				<School :size="65" />
			</template>
		</NcEmptyContent>
	</NcAppContentDetails>
</template>

<script>
import {
	NcAppContentDetails,
	NcEmptyContent,
	NcButton,
} from '@nextcloud/vue'

import Delete from 'vue-material-design-icons/Delete.vue'
import School from 'vue-material-design-icons/School.vue'

import { showError } from '@nextcloud/dialogs'
import { fetchContributorDetails, updateContributor } from './service/ContributorService.js'

export default {
	name: 'ContributorDetails',
	components: {
		// components
		NcAppContentDetails,
		NcEmptyContent,
		NcButton,

		// icons
		Delete,
		School,
	},
	props: {
		contributorId: {
			type: Number,
			required: true,
		},
	},
	data() {
		return {
			contributor: null,
			fixes: [],
			visible: {
				cbButton: false,
			},
			dataModified: false,
		}
	},
	watch: {
		async contributorId(contributorId) {
			// this is required to trigger the update of the various details when
			// contributorId is updated (does not work the first time i.e. through the route)
			this.updateDetails(contributorId)
		},
		async contributor(contributor) {
			if (!contributor || !this.$options || !this.$options.authorListInterface) return

			if (contributor.contributorData.type === 'text') {
				this.$options.authorListInterface.setAuthorListFromText(contributor.contributorData.text)
			} else {
				this.$options.authorListInterface.setAuthorList(contributor.contributorData.contributors)
			}
		},
	},
	async mounted() {
		this.loading = true
		if (this.contributorId) {
			// this is required to trigger the update the various details when
			// the contributorId is first given (for example though the route)
			this.updateDetails(this.contributorId)
		}
		this.loading = false
	},
	methods: {
		setDataModified() {
			this.dataModified = true
		},
		async getContributor(contributorId) {
			try {
				const contributorDetails = await fetchContributorDetails(contributorId)
				return contributorDetails
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch contributor details (route mounting failed)'))
			}
			return null
		},
		async saveChanges() {
			try {
				const response = await updateContributor(
					this.contributor.id, this.contributor.firstName,
					this.contributor.lastNameIsFullName, this.contributor.lastName,
				)
				return response
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch contributor details (route mounting failed)'))
			}
			return null
		},
		async updateDetails(contributorId) {
			if (!contributorId) return
			this.contributor = await this.getContributor(contributorId)
		},
		getName(contributor) {
			const name = contributor.lastNameIsFullName
				? contributor.lastName
				: (contributor.lastName + ' ' + contributor.firstName)
			return name || t('athenaeum', 'New contributor')
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
