<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcListItem :title="source.title ? source.title : t('athenaeum', 'New source')"
		:class="{active: currentSourceId === source.id}"
		:counter-number="source.importance"
		:to="link">
		<template #icon>
			<GoogleScholarIcon size="20" />
		</template>
		<template #subtitle>
			<div v-if="source.description">
				<span>
					{{ source.description }}
				</span>
			</div>
		</template>
		<template #actions>
			<NcActionButton v-if="source.id === -1"
				icon="icon-close"
				@click="cancelNewSource(source)">
				{{ t('athenaeum', 'Cancel source creation') }}
			</NcActionButton>
			<NcActionButton v-else
				icon="icon-delete"
				@click="deleteSource(source)">
				{{ t('athenaeum', 'Delete source') }}
			</NcActionButton>
		</template>
		<template #extra>
			<div><!-- placeholder div to make sure the extra is always available--></div>
		</template>
	</NcListItem>
</template>

<script>

import NcListItem from '@nextcloud/vue/dist/Components/NcListItem.js'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'

import { GoogleScholarIcon } from 'vue-simple-icons'

export default {
	name: 'SourceListItem',
	components: {
		// components
		NcListItem,
		NcActionButton,

		// icons
		GoogleScholarIcon,
	},
	props: {
		source: {
			type: Object,
			required: true,
		},
	},
	computed: {
		link() {
			return {
				name: 'sources_details',
				params: {
					// filter: this.$route.params.filter ? this.$route.params.filter : undefined,
					sourceId: this.source.id,
				},
				exact: true,
			}
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
