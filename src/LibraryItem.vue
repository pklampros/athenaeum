<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcListItem
		:title="item.title ? item.title : t('athenaeum', 'New item')"
		:class="{active: currentItemId === item.id}"
		:counter-number="item.alertImportance"
		:to="link">
		<template #icon>
			<Bookshelf size="20"/>
		</template>
		<template #subtitle>
			<div
				v-if="item.journal">
				<span>
					{{ item.journal }}
				</span>
			</div>
			<div
				v-if="item.authors">
				<span>
					{{ item.authors }}
				</span>
			</div>
		</template>
		<template #extra>
			<div><!-- placeholder div to make sure the extra is always available--></div>
		</template>
	</NcListItem>
</template>

<script>

import NcListItem from '@nextcloud/vue/dist/Components/NcListItem'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton'

import Bookshelf from 'vue-material-design-icons/Bookshelf.vue';

export default {
	name: 'LibraryItem',
	components: {
		// components
		NcListItem,
		NcActionButton,

		// icons
		Bookshelf,
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
				name: 'library_item',
				params: {
					//filter: this.$route.params.filter ? this.$route.params.filter : undefined,
					libraryItemId: this.item.id,
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