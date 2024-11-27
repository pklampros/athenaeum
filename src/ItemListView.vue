<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcAppContent>
		<div slot="list"
			class="items-list">
			<div id="toptitle">
				<h2>
					Item ({{ itemOffset }} - {{ itemOffset + items.length }} /
					{{ totalItems }})
				</h2>
			</div>
			<NcAppContentList class="main-items-list"
				:show-details="true">
				<ItemListItem v-for="item in items"
					:key="item.id"
					:item="item"
					@item-change-folder="itemSendToFolder" />
			</NcAppContentList>
			<div class="items-footer">
				<NcButton aria-label="Previous page"
					style="flex:1"
					:disabled="itemOffset == 0"
					@click="prevPage">
					<template #icon>
						<ChevronLeft :size="18" />
					</template>
				</NcButton>
				<NcButton aria-label="Next page"
					style="flex:1"
					:disabled="(itemOffset + items.length) >= totalItems"
					@click="nextPage">
					<template #icon>
						<ChevronRight :size="18" />
					</template>
				</NcButton>
			</div>
		</div>
		<ItemDetails slot="default"
			:item-id.sync="currentItemId"
			@item-change-folder="itemSendToFolder" />
	</NcAppContent>
</template>

<script>

import {
	NcAppContent,
	NcAppContentList,
	NcButton,
} from '@nextcloud/vue'

import ItemListItem from './ItemListItem.vue'
import ItemDetails from './ItemDetails.vue'

import {
	fetchItems,
	fetchItemSummary,
	itemChangeFolder,
} from './service/ItemService.js'
import { showError } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue'
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue'

import 'toastify-js/src/toastify.css'

import Toastify from 'toastify-js'

export default {
	name: 'ItemListView',
	components: {
		// components
		NcAppContent,
		NcAppContentList,
		NcButton,

		// icons
		ChevronLeft,
		ChevronRight,

		// project components
		ItemListItem,
		ItemDetails,
	},
	data() {
		return {
			items: [],
			totalItems: 0,
			itemOffset: 0,
			itemLimit: 50,
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
		await this.fetchData()
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
		async fetchData() {
			try {
				const newOffset = this.$route.query.itemOffset
					? this.$route.query.itemOffset
					: 0
				const itemData = await fetchItems(
					this.currentFolder,
					newOffset,
				)
				this.items = itemData.items
				this.itemOffset = itemData.offset
				this.totalItems = itemData.totalCount
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
				this.goToNextAvailableItem()
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch items (route mounting failed)'))
			}
			this.loading = false
		},
		goToNextAvailableItem() {
			if (!this.currentItemId && this.items.length > 0) {
				// go directly to the first item
				this.$router.replace({
					name: 'items_details',
					params: {
						folder: this.currentFolder,
						itemId: this.items[0].id,
					},
					query: this.$route.query,
				})
			}
		},
		itemFolderChanged(itemId) {
			let removedIdx = null
			for (const idx in this.items) {
				if (this.items[idx].id === itemId) {
					this.items.splice(idx, 1)
					removedIdx = idx
					break
				}
			}
			if (this.currentItemId === itemId) {
				if (removedIdx > 0 && this.items.length >= removedIdx) {
					// go directly to the next item
					this.$router.replace({
						name: 'items_details',
						params: {
							folder: this.currentFolder,
							itemId: this.items[removedIdx].id,
						},
						query: this.$route.query,
					})
				} else if (this.items.length > 0) {
					// go directly to the next item
					this.$router.replace({
						name: 'items_details',
						params: {
							folder: this.currentFolder,
							itemId: this.items[0].id,
						},
						query: this.$route.query,
					})
				} else {
					this.currentItemId = -1
					this.$router.replace({
						name: 'items',
						params: {
							folder: this.currentFolder,
						},
						query: this.$route.query,
					})
				}
			}
		},
		async itemSendToFolder(itemId, newFolder, message) {
			await itemChangeFolder(itemId, newFolder)
			this.itemFolderChanged(itemId)
			this.showToast(message)
		},
		showToast(message) {
			Toastify({
				text: message,
				duration: 1500,
				close: false,
				gravity: 'bottom',
				position: 'center',
				stopOnFocus: true,
				style: {
					background: '#00000066',
					text: 'white',
				},
			}).showToast()
		},
		prevPage() {
			const newOffset = this.itemOffset - this.itemLimit < 0
				? 0
				: this.itemOffset - this.itemLimit
			this.$router.replace({ query: { itemOffset: newOffset } })
			this.fetchData()
		},
		nextPage() {
			const newOffset = this.itemOffset + this.itemLimit > this.totalItems
				? this.itemOffset
				: this.itemOffset + this.itemLimit
			this.$router.replace({ query: { itemOffset: newOffset } })
			this.fetchData()
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

.items-list {
	display: flex;
	flex-direction: column;
	height: 100%;
}

.main-items-list {
	min-height: inherit;
	max-height: inherit;
}

.items-footer {
	display: flex;
	flex-direction: row;
	width: 100%;
	padding: 0.5em;
}
</style>
