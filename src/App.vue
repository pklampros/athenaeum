<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<div id="content" class="app-athenaeum">
		<NcAppNavigation>
			<ul>
				<NcAppNavigationItem v-if="!loading"
					:name="t('athenaeum', 'Inbox')"
					:disabled="false"
					button-id="inbox-button"
					@click="setViewMode(ViewMode.SCHOLAR_ITEMS)">
					<template #icon>
						<Inbox :size="20" />
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem v-if="!loading"
					:name="t('athenaeum', 'Library')"
					:disabled="false"
					button-id="library-button"
					@click="setViewMode(ViewMode.ITEMS)">
					<template #icon>
						<Bookshelf :size="20"/>
					</template>
				</NcAppNavigationItem>
			</ul>
			<NcAppNavigationNew v-if="!loading"
				:text="t('athenaeum', 'New scholar item')"
				:disabled="false"
				button-id="new-scholar-item-button"
				button-class="icon-add"
				@click="newScholarItem" />
			<NcAppNavigationNew v-if="!loading"
				:text="t('athenaeum', 'New item')"
				:disabled="false"
				button-id="new-item-button"
				button-class="icon-add"
				@click="newItem" />
			<label>File
				<input type="file" id="file" ref="file" v-on:change="handleFileUpload($event)"/>
			</label>
      		<button v-on:click="submitFile()">Submit</button>
		</NcAppNavigation>
		<NcAppContent>
			<div id="toptitle">
				<h2 v-if="viewMode === ViewMode.SCHOLAR_ITEMS">Inbox</h2>
				<h2 v-else-if="viewMode === ViewMode.ITEMS">Library</h2>
			</div>
			<ul v-if="viewMode === ViewMode.SCHOLAR_ITEMS"
				class="main-items-list">
				<ScholarListItem v-for="scholarItem in scholarItems"
					:key="scholarItem.id"
					:scholarItem="scholarItem"
					@click="openScholarItem(scholarItem)">
				</ScholarListItem>
			</ul>
			<NcAppContentList v-else-if="viewMode === ViewMode.ITEMS"
				class="main-items-list">
				<NcListItem v-for="item in items"
					:key="item.id"
					:title="item.title ? item.title : t('athenaeum', 'New item')"
					:class="{active: currentItemId === item.id}"
					@click="openItem(item)">
					<template slot="actions">
						<NcActionButton v-if="item.id === -1"
							icon="icon-close"
							@click="cancelNewItem(item)">
							{{
							t('athenaeum', 'Cancel item creation') }}
						</NcActionButton>
						<NcActionButton v-else
							icon="icon-delete"
							@click="deleteItem(item)">
							{{
							 t('athenaeum', 'Delete item') }}
						</NcActionButton>
					</template>
				</NcListItem>
			</NcAppContentList>

			<ItemFileModal ref="itemFileModal"/>
		</NcAppContent>

		<NcAppSidebar
			v-if="currentScholarItem"
			:title="currentScholarItem.title"
			@close="cancelNewScholarItem()"
			title-placeholder="Item title"
			subtitle="last edited 3 weeks ago">
			<NcAppSidebarTab name="Item info" id="settings-tab">
				<template #icon>
					<Cog :size="20" />
				</template>
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
			</NcAppSidebarTab>
			<NcAppSidebarTab name="Sharing" id="share-tab">
				<template #icon>
					<ShareVariant :size="20" />
				</template>
				Sharing tab content
			</NcAppSidebarTab>
		</NcAppSidebar>
			
	</div>
</template>

<script>

import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem'
import NcAppNavigationNew from '@nextcloud/vue/dist/Components/NcAppNavigationNew'
import NcAppNavigationToggle from '@nextcloud/vue/dist/Components/NcAppNavigationToggle'


import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent'
import NcAppContentList from '@nextcloud/vue/dist/Components/NcAppContentList'

import NcAppSidebar from '@nextcloud/vue/dist/Components/NcAppSidebar'
import NcAppSidebarTab from '@nextcloud/vue/dist/Components/NcAppSidebarTab'

import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton'
import NcListItem from '@nextcloud/vue/dist/Components/NcListItem'
import NcRichText from '@nextcloud/vue/dist/Components/NcRichText'

import Bookshelf from 'vue-material-design-icons/Bookshelf.vue';
import Inbox from 'vue-material-design-icons/Inbox.vue';

import ItemFileModal from './ItemFileModal.vue'
import ScholarListItem from './ScholarListItem.vue'

import '@nextcloud/dialogs/dist/index.css'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import {
	ViewMode
} from "./enums";

export default {
	name: 'App',
	components: {
		NcAppNavigation,
		NcAppNavigationItem,
		NcAppNavigationNew,
		NcAppNavigationToggle,

		NcAppContent,
		NcAppContentList,

		NcAppSidebar,
		NcAppSidebarTab,

		NcActionButton,
		NcListItem,
		NcRichText,

		Bookshelf,
		Inbox,

		ItemFileModal,
		ScholarListItem,
	},
	data() {
		return {
			items: [],
			scholarItems: [],
			currentItemId: null,
			currentScholarItemId: null,
			fileItemTrigger: 0,
			updating: false,
			loading: true,
			ViewMode: ViewMode,
			viewMode: ViewMode.SCHOLAR_ITEMS
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
		handleFileUpload( event ){
			console.log(event);
			this.file = event.target.files[0];
			console.log(this.file);
		},
		submitFile(){
			let formData = new FormData();
			formData.append('file', this.file);
			console.log(this.file);
			console.log(formData.get('file'));
			axios.post( generateUrl('/apps/athenaeum/scholar_items/extractFromEML'), formData, {
				headers: {
					'Content-Type': 'multipart/form-data'
				}
			}).then(function(){
				console.log('SUCCESS!!');
			}).catch(function(){
				console.log('FAILURE!!');
			});
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
		getSubtitle(scholarItem) {
			const authors = scholarItem.authors ? scholarItem.authors : ''
			const journal = scholarItem.journal ? scholarItem.journal : ''
			if (authors && journal) {
				return authors + ' - ' + journal
			}
			return authors + journal
		
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

	#app-content-vue > div.main-items-list {
		max-width: 100%;
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

	#toptitle {
		--athenaeum-navigation-height: 64px;
		display: flex;
		align-items: center;
		min-height: var(--athenaeum-navigation-height);
	    padding: 0 var(--athenaeum-navigation-height);
	}

	.modal__content {
		margin: 20px;
		text-align: center;
	}

	.input-field {
		margin: 12px 0px;
	}

</style>
