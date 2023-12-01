<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcListItem :title="item.title ? item.title : t('athenaeum', 'New item')"
		:class="{active: currentItemId === item.id}"
		:counter-number="item.sourceImportance"
		:to="link">
		<template #icon>
			<GoogleScholarIcon size="20" />
		</template>
		<template #subtitle>
			<div v-if="item.journal">
				<span>
					{{ item.journal }}
				</span>
			</div>
			<div v-if="item.authors">
				<span>
					{{ item.authors }}
				</span>
			</div>
		</template>
		<template #actions>
			<NcActionButton v-if="item.id === -1"
				icon="icon-close"
				@click="cancelNewItem(item)">
				{{ t('athenaeum', 'Cancel item creation') }}
			</NcActionButton>
			<NcActionButton v-else
				icon="icon-delete"
				@click="deleteItem(item)">
				{{ t('athenaeum', 'Delete item') }}
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
	name: 'ItemListItem',
	components: {
		// components
		NcListItem,
		NcActionButton,

		// icons
		GoogleScholarIcon,
	},
	props: {
		item: {
			type: Object,
			required: true,
		},
	},
	computed: {
		link() {
			return {
				name: 'items_details',
				params: {
					// filter: this.$route.params.filter ? this.$route.params.filter : undefined,
					itemId: this.item.id,
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
