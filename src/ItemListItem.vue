<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcListItem :name="item.title ? item.title : t('athenaeum', 'New item')"
		:class="{ active: currentItemId === item.id }"
		:counter-number="item.sourceImportance"
		:to="link">
		<template #icon>
			<GoogleScholarIcon size="20" />
		</template>
		<template #subname>
			<span v-if="item.published">
				{{ item.published }}
			</span>
			<span v-if="item.published && item.authors"> - </span>
			<span v-if="item.authors">
				{{ item.authors }}
			</span>
			<span v-if="(item.authors && item.journal) || (item.published && item.journal)"> - </span>
			<span v-if="item.journal">
				{{ item.journal }}
			</span>
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

import { NcListItem, NcActionButton } from '@nextcloud/vue'

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
