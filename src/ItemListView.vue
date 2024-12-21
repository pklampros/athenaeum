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
					Item ({{ listOffset }} - {{ listOffset + items.length }} /
					{{ totalCount }})
					<NcButton aria-label="Tune"
						style="flex:1"
						@click="tuning = !tuning">
						<template #icon>
							<Tune :size="18" />
						</template>
					</NcButton>
				</h2>
			</div>
			<div v-if="tuning"
				class="list-controls tuner">
				<NcSelect v-bind="orderOptions"
					v-model="orderOptions.value" />
				<NcTextField label="Search in title"
					:value.sync="listQuery" />
				<div class="button-row-right">
					<NcButton aria-label="Apply filters"
						@click="applyFilters">
						Apply
					</NcButton>
					<NcButton aria-label="Cancel filter selection"
						@click="cancelFilters">
						Cancel
					</NcButton>
				</div>
			</div>
			<NcAppContentList class="main-items-list"
				:show-details="true">
				<ItemListItem v-for="item in items"
					:key="item.id"
					:item="item"
					@item-change-folder="itemSendToFolder" />
			</NcAppContentList>
			<div class="list-controls items-footer">
				<NcButton aria-label="First page"
					style="flex:1"
					:disabled="listOffset == 0"
					@click="firstPage">
					<template #icon>
						<PageFirst :size="18" />
					</template>
				</NcButton>
				<NcButton aria-label="Back multiple pages"
					style="flex:1"
					:disabled="listOffset == 0"
					@click="backMultiplePages">
					<template #icon>
						<ChevronDoubleLeft :size="18" />
					</template>
				</NcButton>
				<NcButton aria-label="Previous page"
					style="flex:1"
					:disabled="listOffset == 0"
					@click="prevPage">
					<template #icon>
						<ChevronLeft :size="18" />
					</template>
				</NcButton>
				<NcButton aria-label="Next page"
					style="flex:1"
					:disabled="(listOffset + items.length) >= totalCount"
					@click="nextPage">
					<template #icon>
						<ChevronRight :size="18" />
					</template>
				</NcButton>
				<NcButton aria-label="Forward multiple pages"
					style="flex:1"
					:disabled="(listOffset + items.length) >= totalCount"
					@click="forwardMultiplePages">
					<template #icon>
						<ChevronDoubleRight :size="18" />
					</template>
				</NcButton>
				<NcButton aria-label="Last page"
					style="flex:1"
					:disabled="(listOffset + items.length) >= totalCount"
					@click="lastPage">
					<template #icon>
						<PageLast :size="18" />
					</template>
				</NcButton>
			</div>
		</div>
		<ItemDetails slot="default"
			:item-summary.sync="currentItemSummary"
			@item-change-folder="itemSendToFolder" />
	</NcAppContent>
</template>

<script>

import {
	NcAppContent,
	NcAppContentList,
	NcButton,
	NcSelect,
	NcTextField,
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
import ChevronDoubleLeft from 'vue-material-design-icons/ChevronDoubleLeft.vue'
import ChevronDoubleRight from 'vue-material-design-icons/ChevronDoubleRight.vue'
import PageFirst from 'vue-material-design-icons/PageFirst.vue'
import PageLast from 'vue-material-design-icons/PageLast.vue'
import Tune from 'vue-material-design-icons/Tune.vue'

import 'toastify-js/src/toastify.css'

import Toastify from 'toastify-js'

const orderOptions = {
	inputLabel: 'Sort order',
	multiple: true,
	closeOnSelect: false,
	options: [
		{
			id: 'date_added',
			label: 'Date added (ascending)',
		},
		{
			id: '-date_added',
			label: 'Date added (descending)',
		},
		{
			id: 'date_modified',
			label: 'Date modified (ascending)',
		},
		{
			id: '-date_modified',
			label: 'Date modified (descending)',
		},
		{
			id: 'source_importance',
			label: 'Source importance (ascending)',
		},
		{
			id: '-source_importance',
			label: 'Source importance (descending)',
		},
	],
	value: [
		{
			id: 'date_added',
			label: 'Date added (ascending)',
		},
		{
			id: '-source_importance',
			label: 'Source importance (descending)',
		},
	],
}

export default {
	name: 'ItemListView',
	components: {
		// components
		NcAppContent,
		NcAppContentList,
		NcButton,
		NcSelect,
		NcTextField,

		// icons
		ChevronLeft,
		ChevronRight,
		ChevronDoubleLeft,
		ChevronDoubleRight,
		PageFirst,
		PageLast,
		Tune,

		// project components
		ItemListItem,
		ItemDetails,
	},
	data() {
		return {
			items: [],
			totalCount: 0,
			listOffset: 0,
			listLimit: 50,
			listReplenish: 5,
			listOrderBy: this.$route.query.listOrderBy
				? this.$route.query.listOrderBy
				: ['date_added', '-source_importance'],
			updating: false,
			loading: true,
			tuning: false,
			orderOptions,
			listQuery: this.$route.query.listQuery
				? this.$route.query.listQuery
				: '',
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
		currentItemSummary() {
			if (!this.currentItemId) {
				return null
			}
			const itemSummary = this.items.find((item) => item.id === this.currentItemId)
			if (!itemSummary) {
				return { id: this.currentItemId }
			}
			return itemSummary
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
		applyFilters() {
			let modified = false
			let newRouteQuery = { ...this.$route.query }
			if (this.listQuery !== this.$route.query.listQuery) {
				newRouteQuery = { ...newRouteQuery, listQuery: this.listQuery }
				modified = true
			}
			this.listOrderBy = orderOptions.value.map(v => v.id)
			if (this.listOrderBy.join(',') !== this.$route.query.listOrderBy.join(',')) {
				newRouteQuery = { ...newRouteQuery, listOrderBy: this.listOrderBy }
				modified = true
			}
			if (modified) {
				this.$router.push({ query: { ...newRouteQuery } })
				this.fetchData()
			}
		},
		cancelFilters() {
			this.listQuery = ''
			this.tuning = false
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
		fetchUpdateItemSummary(itemIdx) {
			fetchItemSummary(this.items[itemIdx].id).then((resp) => {
				const contributions = resp.data.contributions
				const sourceInfoExtra = resp.data.sourceInfo.length > 0
					&& 'extra' in resp.data.sourceInfo[0]
					? resp.data.sourceInfo[0].extra
					: {}
				if (contributions.length !== 0) {
					// item.authors = contributions.map(c => c.contributor_name_display).join(',')
					this.$set(this.items[itemIdx], 'authors', contributions.map(c => c.contributor_name_display).join(','))
				} else if ('authors' in sourceInfoExtra) {
					this.$set(this.items[itemIdx], 'authors', sourceInfoExtra.authors)
				}
				if ('journal' in sourceInfoExtra) {
					this.$set(this.items[itemIdx], 'journal', sourceInfoExtra.journal)
				}
				if ('published' in sourceInfoExtra) {
					this.$set(this.items[itemIdx], 'published', sourceInfoExtra.published)
				}
			}).catch((error) => {
				showError(t('athenaeum', 'Could not fetch items (' + error + ')'))
			})
		},
		async fetchData() {
			try {
				const newOffset = this.$route.query.listOffset
					? this.$route.query.listOffset
					: 0
				this.listOrderBy = this.$route.query.listOrderBy
					? this.$route.query.listOrderBy
					: ['date_added', '-source_importance']
				this.listQuery = this.$route.query.listQuery
					? this.$route.query.listQuery
					: ''

				const itemData = await fetchItems(
					this.currentFolder,
					newOffset,
					this.listLimit,
					this.listOrderBy,
					this.listQuery,
				)
				this.items = itemData.items
				this.listOffset = itemData.offset
				this.totalCount = itemData.totalCount
				for (const i in this.items) {
					this.fetchUpdateItemSummary(i)
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
		replenishItems() {
			const newOffset = this.listOffset + this.items.length
			const replenish = Math.min(this.listReplenish,
				this.totalCount - newOffset)
			if ((this.listLimit - this.items.length) >= replenish
				&& (newOffset + replenish) < this.totalCount) {
				fetchItems(
					this.currentFolder,
					newOffset,
					replenish,
					this.listOrderBy,
				).then((itemData) => {
					this.items.push(...itemData.items)

					this.totalCount = itemData.totalCount
					for (let i = this.listLimit - replenish;
						i < this.listLimit; i++) {
						this.fetchUpdateItemSummary(i)
					}
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
			this.replenishItems()
			if (this.currentItemId === itemId) {
				if (removedIdx > 0 && this.items.length > removedIdx) {
					// go directly to the next item
					this.$router.push({
						name: 'items_details',
						params: {
							folder: this.currentFolder,
							itemId: this.items[removedIdx].id,
						},
						query: this.$route.query,
					})
				} else if (removedIdx > 0 && removedIdx >= this.items.length) {
					// go directly to the next item
					this.$router.push({
						name: 'items_details',
						params: {
							folder: this.currentFolder,
							itemId: this.items[this.items.length - 1].id,
						},
						query: this.$route.query,
					})
				} else if (this.items.length > 0) {
					// go directly to the first item
					this.$router.push({
						name: 'items_details',
						params: {
							folder: this.currentFolder,
							itemId: this.items[0].id,
						},
						query: this.$route.query,
					})
				} else if (this.items.length === 0) {
					// go to the previous page
					if (this.listOffset > 0) {
						this.setNewItemOffset(this.listOffset - this.listLimit)
					}
				} else {
					this.currentItemId = -1
					this.$router.push({
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
		setNewItemOffset(newOffset) {
			if (newOffset < 0) {
				newOffset = 0
			}
			if (newOffset >= this.totalCount) {
				newOffset = this.listLimit
					* Math.floor(this.totalCount / this.listLimit)
			}
			this.$router.push({ query: { listOffset: newOffset } })
			this.fetchData()
		},
		firstPage() {
			this.setNewItemOffset(0)
		},
		backMultiplePages() {
			this.setNewItemOffset(Math.floor((Math.floor(
				this.listOffset / this.listLimit) - 1)
				* 0.5) * this.listLimit)
		},
		prevPage() {
			this.setNewItemOffset(this.listOffset - this.listLimit)
		},
		nextPage() {
			this.setNewItemOffset(this.listOffset + this.listLimit)
		},
		forwardMultiplePages() {
			this.setNewItemOffset(Math.floor((Math.floor(
				this.listOffset / this.listLimit) + 1
				+ Math.floor(this.totalCount / this.listLimit))
				* 0.5) * this.listLimit)
		},
		lastPage() {
			this.setNewItemOffset(this.totalCount)
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

.list-controls {
	padding: 0.5em;
	width: 100%;
}

.tuner {
	display: flex;
	flex-direction: column;
}

.button-row-right {
	display: flex;
	flex-direction: row-reverse;
}

.items-footer {
	display: flex;
	flex-direction: row;
}
</style>
