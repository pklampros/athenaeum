<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcListItem
		:title="scholarItem.title ? scholarItem.title : t('athenaeum', 'New scholar item')"
		:class="{active: currentScholarItemId === scholarItem.id}"
		:counter-number="scholarItem.alertImportance"
		:to="link">
		<template #icon>
			<google-scholar-icon size="20"/>
		</template>
		<template #subtitle>
			<div
				v-if="scholarItem.journal">
				<span>
					{{ scholarItem.journal }}
				</span>
			</div>
			<div
				v-if="scholarItem.authors">
				<span>
					{{ scholarItem.authors }}
				</span>
			</div>
		</template>
		<template #actions>
			<NcActionButton
				@click="itemFileModal.shelveItem(scholarItem)">
				{{ t('athenaeum', 'Shelve...') }}
				<template #icon>
					<Bookshelf :size="20"/>
				</template>
			</NcActionButton>
			<NcActionButton v-if="scholarItem.id === -1"
				icon="icon-close"
				@click="cancelNewScholarItem(scholarItem)">
				{{ t('athenaeum', 'Cancel item creation') }}
			</NcActionButton>
			<NcActionButton v-else
				icon="icon-delete"
				@click="deleteScholarItem(scholarItem)">
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
	name: 'ScholarItem',
	components: {
		// components
		NcListItem,
		NcActionButton,

		// icons
		Bookshelf,
		GoogleScholarIcon,
	},
	props: {
		scholarItem: {
			type: Object,
			required: true,
		},
		itemFileModal: {
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
					scholarItemId: this.scholarItem.id,
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