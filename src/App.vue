<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<div id="content" class="app-athenaeum">
		<NcAppNavigation>
			<ul v-if="!loading">
				<NcAppNavigationItem
					:name="t('athenaeum', 'Sources')"
					:disabled="false"
					:to="'/sources'" >
					<template #icon>
						<Bookshelf :size="20"/>
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem 
					v-for="folder in folders"
					:key="folder.id"
					:name="t('athenaeum', folder.name)"
					:disabled="false"
					:to="'/items/' + folder.path" >
					<template #icon>
						<Inbox v-if="folder.isinbox" :size="20" />
						<Bookshelf v-else :size="20"/>
					</template>
				</NcAppNavigationItem>
			</ul>
			<NcAppNavigationNew v-if="!loading"
				:text="t('athenaeum', 'New inbox item')"
				:disabled="false"
				button-id="new-inbox-item-button"
				button-class="icon-add"
				@click="newInboxItem" />
			<NcAppNavigationNew v-if="!loading"
				:text="t('athenaeum', 'New item')"
				:disabled="false"
				button-id="new-item-button"
				button-class="icon-add"
				@click="newItem" />
			<label>File
				<input type="file" id="file" ref="file" v-on:change="handleFileUpload($event)" multiple/>
			</label>
      		<button v-on:click="submitFiles()">Submit</button>
		</NcAppNavigation>

		<ItemView v-if="isItemView()" :key="currentView"/>
		<SourceView v-if="isSourceView()" :key="currentView"/>
			
	</div>
</template>

<script>

import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem'
import NcAppNavigationNew from '@nextcloud/vue/dist/Components/NcAppNavigationNew'

import Bookshelf from 'vue-material-design-icons/Bookshelf.vue';
import Inbox from 'vue-material-design-icons/Inbox.vue';

import ItemView from './ItemView.vue'
import SourceView from './SourceView.vue'

import '@nextcloud/dialogs/dist/index.css'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

import { fetchFolders } from './service/FolderService'

import {
	ViewMode
} from "./enums";

export default {
	name: 'App',
	components: {
		// components
		NcAppNavigation,
		NcAppNavigationItem,
		NcAppNavigationNew,

		// icons
		Bookshelf,
		Inbox,

		// project components
		ItemView,
		SourceView,
	},
	data() {
		return {
			fileItemTrigger: 0,
			ViewMode: ViewMode,
			folders: [],
			loading: false,
			uploading: false
		}
	},
	computed: {
		currentView() {
			console.log("currentView", this.$route);
			return {
				"view": this.$route.name,
				"folder": this.$route.params.folder
			};
		},
	},
	async mounted() {
		this.loading = true
		try {
			this.folders = await fetchFolders();
		} catch (e) {
			console.error(e)
			showError(t('athenaeum', 'Could not fetch folders (route mounting failed)'))
		}
		this.loading = false
	},
	methods: {

		handleFileUpload( event ){
			console.log(event);
			this.files = event.target.files;
			console.log(this.file);
		},
		submitFiles(){
			this.uploading = true;
			let formData = new FormData();
			for (let i = 0; i < this.files.length; i++) {
				formData.append(i, this.files[i]);
			};
			formData.set('fileCount', this.files.length);
			let thisClass = this;
			axios.post(
				generateUrl('/apps/athenaeum/inbox_items/extractFromEML'),
				formData,
				{
					headers: {
						'Content-Type': 'multipart/form-data'
					}
				}).then(function(){
					console.log('SUCCESS!!');
				}).catch(function(){
					console.log('FAILURE!!');
				}).finally(function() {
					thisClass.uploading = false;
				});
		},
		isItemView() {
			return this.currentView.view === ViewMode.ITEMS ||
				this.currentView.view === ViewMode.ITEMS_DETAILS;
		},
		isSourceView() {
			return this.currentView.view === ViewMode.SOURCES ||
				this.currentView.view === ViewMode.SOURCES_DETAILS;
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
