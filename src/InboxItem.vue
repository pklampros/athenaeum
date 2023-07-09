<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcListItem
		:title="inboxItem.title ? inboxItem.title : t('athenaeum', 'New inbox item')"
		:class="{active: currentInboxItemId === inboxItem.id}"
		:counter-number="inboxItem.alertImportance"
		:to="link">
		<template #icon>
			<google-scholar-icon size="20"/>
		</template>
		<template #subtitle>
			<div
				v-if="inboxItem.journal">
				<span>
					{{ inboxItem.journal }}
				</span>
			</div>
			<div
				v-if="inboxItem.authors">
				<span>
					{{ inboxItem.authors }}
				</span>
			</div>
		</template>
		<template #actions>
			<NcActionButton v-if="inboxItem.id === -1"
				icon="icon-close"
				@click="cancelNewInboxItem(inboxItem)">
				{{ t('athenaeum', 'Cancel item creation') }}
			</NcActionButton>
			<NcActionButton v-else
				icon="icon-delete"
				@click="deleteInboxItem(inboxItem)">
				{{ t('athenaeum', 'Delete item') }}
			</NcActionButton>
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
import { GoogleScholarIcon } from 'vue-simple-icons'

export default {
	name: 'InboxItem',
	components: {
		// components
		NcListItem,
		NcActionButton,

		// icons
		Bookshelf,
		GoogleScholarIcon,
	},
	props: {
		inboxItem: {
			type: Object,
			required: true,
		},
	},
	computed: {
		link() {
			return {
				name: 'inbox_item',
				params: {
					//filter: this.$route.params.filter ? this.$route.params.filter : undefined,
					inboxItemId: this.inboxItem.id,
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