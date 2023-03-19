<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<div id="content" class="app-athenaeum">
		<AppNavigation>
			<AppNavigationNew v-if="!loading"
				:text="t('athenaeum', 'New scholar item')"
				:disabled="false"
				button-id="new-scholar-item-button"
				button-class="icon-add"
				@click="newScholarItem" />
			<ul>
				<AppNavigationItem v-for="scholarItem in scholarItems"
					:key="scholarItem.id"
					:title="scholarItem.title ? scholarItem.title : t('athenaeum', 'New scholar item')"
					:class="{active: currentScholarItemId === scholarItem.id}"
					@click="openScholarItem(scholarItem)">
					<template slot="actions">
						<ActionButton v-if="scholarItem.id === -1"
							icon="icon-close"
							@click="cancelNewScholarItem(scholarItem)">
							{{
							t('athenaeum', 'Cancel item creation') }}
						</ActionButton>
						<ActionButton v-else
							icon="icon-delete"
							@click="deleteScholarItem(scholarItem)">
							{{
							 t('athenaeum', 'Delete item') }}
						</ActionButton>
					</template>
				</AppNavigationItem>
			</ul>
			<AppNavigationNew v-if="!loading"
				:text="t('athenaeum', 'New item')"
				:disabled="false"
				button-id="new-item-button"
				button-class="icon-add"
				@click="newItem" />
			<ul>
				<AppNavigationItem v-for="item in items"
					:key="item.id"
					:title="item.title ? item.title : t('athenaeum', 'New item')"
					:class="{active: currentItemId === item.id}"
					@click="openItem(item)">
					<template slot="actions">
						<ActionButton v-if="item.id === -1"
							icon="icon-close"
							@click="cancelNewItem(item)">
							{{
							t('athenaeum', 'Cancel item creation') }}
						</ActionButton>
						<ActionButton v-else
							icon="icon-delete"
							@click="deleteItem(item)">
							{{
							 t('athenaeum', 'Delete item') }}
						</ActionButton>
					</template>
				</AppNavigationItem>
			</ul>
		</AppNavigation>
		<AppContent>
			<div v-if="currentScholarItem">
				<input ref="url"
					v-model="currentScholarItem.url"
					type="text"
					:disabled="updating">
				<input ref="title"
					v-model="currentScholarItem.title"
					type="text"
					:disabled="updating">
				<input ref="authors"
					v-model="currentScholarItem.authors"
					type="text"
					:disabled="updating">
				<input ref="journal"
					v-model="currentScholarItem.journal"
					type="text"
					:disabled="updating">
				<input ref="published"
					v-model="currentScholarItem.published"
					type="text"
					:disabled="updating">
				<input type="button"
					class="primary"
					:value="t('athenaeum', 'Save')"
					:disabled="updating || !saveScholarItemPossible"
					@click="saveScholarItem">
			</div>
			<div v-else-if="currentItem">
				<input ref="title"
					v-model="currentItem.title"
					type="text"
					:disabled="updating">
				<input type="button"
					class="primary"
					:value="t('athenaeum', 'Save')"
					:disabled="updating || !savePossible"
					@click="saveItem">
			</div>
			<div v-else id="emptycontent">
				<div class="icon-file" />
				<h2>{{
				 t('athenaeum', 'Create an item to get started') }}</h2>
			</div>
		</AppContent>
	</div>
</template>

<script>
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'

import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

export default {
	name: 'App',
	components: {
		ActionButton,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppNavigationNew,
	},
	data() {
		return {
			items: [],
			scholarItems: [],
			currentItemId: null,
			currentScholarItemId: null,
			updating: false,
			loading: true,
		}
	},
	computed: {
		/**
		 * Return the currently selected item object
		 * @returns {Object|null}
		 */
		currentItem() {
			if (this.currentItemId === null) {
				return null
			}
			return this.items.find((item) => item.id === this.currentItemId)
		},

		currentScholarItem() {
			if (this.currentScholarItemId === null) {
				return null
			}
			return this.scholarItems.find((scholarItem) => scholarItem.id === this.currentScholarItemId)
		},

		/**
		 * Returns true if an item is selected and its title is not empty
		 * @returns {Boolean}
		 */
		savePossible() {
			return this.currentItem && this.currentItem.title !== ''
		},
		saveScholarItemPossible() {
			return this.currentScholarItem && this.currentScholarItem.title !== ''
		},
	},
	/**
	 * Fetch list of items when the component is loaded
	 */
	async mounted() {
		try {
			const response = await axios.get(generateUrl('/apps/athenaeum/items'))
			this.items = response.data
		} catch (e) {
			console.error(e)
			showError(t('athenaeum', 'Could not fetch items (route mounting failed)'))
		}
		try {
			const response = await axios.get(generateUrl('/apps/athenaeum/scholar_items'))
			this.scholarItems = response.data
		} catch (e) {
			console.error(e)
			showError(t('athenaeum', 'Could not fetch items (route mounting failed)'))
		}
		this.loading = false
	},

	methods: {
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
		openScholarItem(scholarItem) {
			if (this.updating) {
				return
			}
			this.currentScholarItemId = scholarItem.id
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
		saveScholarItem() {
			if (this.currentScholarItemId === -1) {
				this.createScholarItem(this.currentScholarItem)
			} else {
				this.updateScholarItem(this.currentScholarItem)
			}
		},
		/**
		 * Create a new item and focus the item content field automatically
		 * The item is not yet saved, therefore an id of -1 is used until it
		 * has been persisted in the backend
		 */
		newItem() {
			if (this.currentItemId !== -1) {
				this.currentItemId = -1
				this.items.push({
					id: -1,
					title: '',
					itemTypeId: 1
				})
				this.$nextTick(() => {
					this.$refs.title.focus()
				})
			}
		},
		newScholarItem() {
			if (this.currentScholarItemId !== -1) {
				this.currentScholarItemId = -1
				this.scholarItems.push({
					id: -1,
					url: '',
					title: '',
					authors: '',
					journal: '',
					published: ''
				})
				this.$nextTick(() => {
					this.$refs.title.focus()
				})
			}
		},
		/**
		 * Abort creating a new item
		 */
		cancelNewItem() {
			this.items.splice(this.items.findIndex((item) => item.id === -1), 1)
			this.currentItemId = null
		},
		cancelNewScholarItem() {
			this.scholarItems.splice(this.scholarItems.findIndex((scholarItem) => scholarItem.id === -1), 1)
			this.currentScholarItemId = null
		},
		/**
		 * Create a new item by sending the information to the server
		 * @param {Object} item Item object
		 */
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
		async createScholarItem(scholarItem) {
			this.updating = true
			try {
				const response = await axios.post(generateUrl('/apps/athenaeum/scholar_items'), scholarItem)
				const index = this.scholarItems.findIndex((match) => match.id === this.currentScholarItemId)
				this.$set(this.scholarItems, index, response.data)
				this.currentScholarItemId = response.data.id
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not create the scholar item'))
			}
			this.updating = false
		},
		/**
		 * Update an existing item on the server
		 * @param {Object} item Item object
		 */
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
		async updateScholarItem(scholarItem) {
			this.updating = true
			try {
				await axios.put(generateUrl(`/apps/athenaeum/scholar_items/${scholarItem.id}`), scholarItem)
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not update the scholar item'))
			}
			this.updating = false
		},
		/**
		 * Delete an item, remove it from the frontend and show a hint
		 * @param {Object} item Item object
		 */
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
		async deleteScholarItem(scholarItem) {
			try {
				await axios.delete(generateUrl(`/apps/athenaeum/scholar_items/${scholarItem.id}`))
				this.scholarItems.splice(this.scholarItems.indexOf(scholarItem), 1)
				if (this.currentScholarItemId === scholarItem.id) {
					this.currentScholarItemId = null
				}
				showSuccess(t('athenaeum', 'Scholar Item deleted'))
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not delete the scholar item'))
			}
		},
	},
}
</script>
<style scoped>
	#app-content > div {
		width: 100%;
		height: 100%;
		padding: 20px;
		display: flex;
		flex-direction: column;
		flex-grow: 1;
	}

	input[type='text'] {
		width: 100%;
	}

</style>
