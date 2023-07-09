<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcAppContent>
		<div slot="list" class='header__button'>
			<div id="toptitle">
				<h2 >Library</h2>
			</div>
			<NcAppContentList
				class="main-items-list"
				:show-details="true" >
				<LibraryItem v-for="item in items"
					:key="item.id"
					:item="item">
				</LibraryItem>
			</NcAppContentList>
		</div>

		<LibraryItemDetails slot="default" />
	</NcAppContent>
</template>

<script>

import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent'
import NcAppContentList from '@nextcloud/vue/dist/Components/NcAppContentList'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton'
import NcListItem from '@nextcloud/vue/dist/Components/NcListItem'

import LibraryItem from './LibraryItem.vue'
import LibraryItemDetails from './LibraryItemDetails.vue'

import { fetchLibraryItems } from './service/LibraryItemService'

export default {
	name: 'LibraryItemList',
	components: {
		// components
		NcAppContent,
		NcAppContentList,
		NcActionButton,
		NcListItem,

		// project components
		LibraryItem,
		LibraryItemDetails,
	},
	data() {
		return {
			items: [],
			currentItemId: null,
			updating: false,
			loading: true,
		}
	},
	computed: {
		currentItem() {
			if (this.currentItemId === null) {
				return null
			}
			return this.items.find((item) => item.id === this.currentItemId)
		},
		savePossible() {
			return this.currentItem && this.currentItem.title !== ''
		},
	},
	async mounted() {
		try {
			this.items = await fetchLibraryItems();
		} catch (e) {
			console.error(e)
			showError(t('athenaeum', 'Could not fetch items (route mounting failed)'))
		}
		this.loading = false
	},

	methods: {

		setViewMode(newViewMode) {
			this.viewMode = newViewMode;
		},

		/**
		 * Create a new item and focus the item content field automatically
		 * @param {Object} item Item object
		 */
		openItem(item) {
			if (this.updating) {
				return
			}
			this.currentItemId = item.id
			this.$nextTick(() => {
				this.$refs.title.focus()
			})
		},
		/**
		 * Action tiggered when clicking the save button
		 * create a new item or save
		 */
		saveItem() {
			if (this.currentItemId === -1) {
				this.createItem(this.currentItem)
			} else {
				this.updateItem(this.currentItem)
			}
		},
		newItem() {
			if (this.currentItemId !== -1) {
				this.currentItemId = -1
				this.items.push({
					id: -1,
					title: '',
					itemTypeId: 1,
					folderId: 1
				})
				this.$nextTick(() => {
					this.$refs.title.focus()
				})
			}
		},
		cancelNewItem() {
			this.items.splice(this.items.findIndex((item) => item.id === -1), 1)
			this.currentItemId = null
		},
		async createItem(item) {
			this.updating = true
			try {
				const response = await axios.post(generateUrl('/apps/athenaeum/items'), item)
				const index = this.items.findIndex((match) => match.id === this.currentItemId)
				this.$set(this.items, index, response.data)
				this.currentItemId = response.data.id
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not create the item'))
			}
			this.updating = false
		},
		async updateItem(item) {
			this.updating = true
			try {
				await axios.put(generateUrl(`/apps/athenaeum/items/${item.id}`), item)
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not update the item'))
			}
			this.updating = false
		},
		async deleteItem(item) {
			try {
				await axios.delete(generateUrl(`/apps/athenaeum/items/${item.id}`))
				this.items.splice(this.items.indexOf(item), 1)
				if (this.currentItemId === item.id) {
					this.currentItemId = null
				}
				showSuccess(t('athenaeum', 'Item deleted'))
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not delete the item'))
			}
		},
	},
}
</script>
<style scoped>

	input[type='text'] {
		width: 100%;
	}

	#toptitle {
		--athenaeum-navigation-height: 64px;
		display: flex;
		align-items: center;
		min-height: var(--athenaeum-navigation-height);
	    padding: 0 var(--athenaeum-navigation-height);
	}

	.input-field {
		margin: 12px 0px;
	}
	
	:deep(.app-content-wrapper) {
		overflow: auto;
	}

	.header__button {
		display: flex;
		flex: 1 0 0;
		flex-direction: column;
		height: calc(100vh - var(--header-height));
	}

</style>
