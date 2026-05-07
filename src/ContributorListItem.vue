<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcListItem :name="getName(contributor)"
		:class="{ active: currentContributorId === contributor.id }"
		:counter-number="contributor.items"
		:to="link">
		<template #icon>
			<GoogleScholarIcon size="20" />
		</template>
		<template #subname>
			<div v-if="contributor.description">
				<span>
					{{ contributor.description }}
				</span>
			</div>
		</template>
		<template #actions>
			<NcActionButton v-if="contributor.id === -1"
				icon="icon-close"
				@click="cancelNewContributor(contributor)">
				{{ t('athenaeum', 'Cancel contributor creation') }}
			</NcActionButton>
			<NcActionButton v-else
				icon="icon-delete"
				@click="deleteContributor(contributor)">
				{{ t('athenaeum', 'Delete contributor') }}
			</NcActionButton>
		</template>
		<template #extra>
			<div><!-- placeholder div to make sure the extra is always available--></div>
		</template>
	</NcListItem>
</template>

<script>

import { NcListItem, NcActionButton } from '@nextcloud/vue'

import { GoogleScholarIcon } from 'vue3-simple-icons'

export default {
	name: 'ContributorListItem',
	components: {
		// components
		NcListItem,
		NcActionButton,

		// icons
		GoogleScholarIcon,
	},
	props: {
		contributor: {
			type: Object,
			required: true,
		},
	},
	computed: {
		link() {
			return {
				name: 'contributors_details',
				params: {
					// filter: this.$route.params.filter ? this.$route.params.filter : undefined,
					contributorId: this.contributor.id,
				},
				exact: true,
			}
		},
	},
	methods: {
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
:deep(.list-item-content__wrapper) {
	margin-top: 9px;
}

:deep(.list-item__extra) {
	margin-top: 11px;
}

.list-item__wrapper {
	list-style: none;
}
</style>
