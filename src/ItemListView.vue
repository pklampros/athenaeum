<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcAppContent>
		<div slot="list"
			class="header__button">
			<div id="toptitle">
				<h2>Item</h2>
			</div>
			<NcAppContentList class="main-items-list"
				:show-details="true">
				<ItemListItem v-for="item in items"
					:key="item.id"
					:item="item" />
			</NcAppContentList>
		</div>
		<ItemDetails slot="default"
			:item-id="currentItemId" />
	</NcAppContent>
</template>

<script>

import { NcAppContent, NcAppContentList } from '@nextcloud/vue'

import ItemListItem from './ItemListItem.vue'
import ItemDetails from './ItemDetails.vue'

import { fetchItems, fetchItemSummary } from './service/ItemService.js'
import { showError } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
	name: 'ItemListView',
	components: {
		// components
		NcAppContent,
		NcAppContentList,

		// project components
		ItemListItem,
		ItemDetails,
	},
	data() {
		return {
			items: [],
			// currentItemId: null,
			updating: false,
			loading: true,
		}
	},
	computed: {
		currentFolder() {
			return this.$route.params.folder
		},
		currentItemId() {
			return parseInt(this.$route.params.itemId, null)
		},
		currentItem() {
			if (!this.currentItemId) {
				return null
			}
			return this.items.find((item) => item.id === this.currentItemId)
		},

		saveItemPossible() {
			return this.currentItem && this.currentItem.title !== ''
		},
	},
	async mounted() {
		try {
			const itemData = await fetchItems(this.currentFolder)
			this.items = itemData.items
			for (const i in this.items) {
				fetchItemSummary(this.items[i].id).then((resp) => {
					const contributions = resp.data.contributions
					const sourceInfoExtra = resp.data.sourceInfo.length > 0
						&& 'extra' in resp.data.sourceInfo[0]
						? resp.data.sourceInfo[0].extra
						: {}
					if (contributions.length !== 0) {
						// item.authors = contributions.map(c => c.contributor_name_display).join(',')
						this.$set(this.items[i], 'authors', contributions.map(c => c.contributor_name_display).join(','))
					} else if ('authors' in sourceInfoExtra) {
						this.$set(this.items[i], 'authors', sourceInfoExtra.authors)
					}
					if ('journal' in sourceInfoExtra) {
						this.$set(this.items[i], 'journal', sourceInfoExtra.journal)
					}
					if ('published' in sourceInfoExtra) {
						this.$set(this.items[i], 'published', sourceInfoExtra.published)
					}
				}).catch((error) => {
					throw convertAxiosError(error)
				})
			}
			if (!this.currentItemId && this.items.length > 0) {
				// go directly to the first item
				this.$router.push({
					name: 'items_details',
					params: {
						folder: this.currentFolder,
						itemId: this.items[0].id,
					},
				})
			}
		} catch (e) {
			console.error(e)
			showError(t('athenaeum', 'Could not fetch items (route mounting failed)'))
		}
		this.loading = false
	},

	methods: {
		newItem() {
			if (this.currentItemId !== -1) {
				this.currentItemId = -1
				this.items.push({
					id: -1,
					url: '',
					title: '',
					authors: '',
					journal: '',
					published: '',
				})
				this.$nextTick(() => {
					this.$refs.title.focus()
				})
			}
		},
		getSubtitle(item) {
			const authors = item.authors ? item.authors : ''
			const journal = item.journal ? item.journal : ''
			if (authors && journal) {
				return authors + ' - ' + journal
			}
			return authors + journal

		},
		cancelNewItem() {
			this.items.splice(this.items.findIndex((item) => item.id === -1), 1)
			this.currentItemId = null
		},
		async createItem(item) {
			this.updating = true
			try {
				const response = await axios.post(generateUrl('/apps/athenaeum/res/items'), item)
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
				await axios.put(generateUrl(`/apps/athenaeum/res/items/${item.id}`), item)
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not update the item'))
			}
			this.updating = false
		},
		async deleteItem(item) {
			try {
				await axios.delete(generateUrl(`/apps/athenaeum/res/items/${item.id}`))
				this.items.splice(this.items.indexOf(item), 1)
				if (this.currentItemId === item.id) {
					this.currentItemId = null
				}
				// showSuccess(t('athenaeum', 'Scholar Item deleted'))
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
