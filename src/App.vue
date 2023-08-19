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
					to="/items/inbox" >
					<template #icon>
						<Inbox :size="20" />
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem v-if="!loading"
					:name="t('athenaeum', 'Inbox/Decide Later')"
					:disabled="false"
					button-id="decide-later-button"
					to="/items/inbox:decide_later" >
					<template #icon>
						<Inbox :size="20" />
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem v-if="!loading"
					:name="t('athenaeum', 'Library')"
					:disabled="false"
					button-id="library-button"
					to="/items/library" >
					<template #icon>
						<Bookshelf :size="20"/>
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
				<input type="file" id="file" ref="file" v-on:change="handleFileUpload($event)"/>
			</label>
      		<button v-on:click="submitFile()">Submit</button>
		</NcAppNavigation>

		<ItemView v-if="isItemView()" :key="currentView"/>
			
	</div>
</template>

<script>

import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem'
import NcAppNavigationNew from '@nextcloud/vue/dist/Components/NcAppNavigationNew'

import Bookshelf from 'vue-material-design-icons/Bookshelf.vue';
import Inbox from 'vue-material-design-icons/Inbox.vue';

import ItemView from './ItemView.vue'

import '@nextcloud/dialogs/dist/index.css'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

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
	},
	data() {
		return {
			fileItemTrigger: 0,
			ViewMode: ViewMode,
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

	methods: {

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
			axios.post( generateUrl('/apps/athenaeum/inbox_items/extractFromEML'), formData, {
				headers: {
					'Content-Type': 'multipart/form-data'
				}
			}).then(function(){
				console.log('SUCCESS!!');
			}).catch(function(){
				console.log('FAILURE!!');
			});
		},
		isItemView() {
			return this.currentView.view === ViewMode.ITEMS ||
				this.currentView.view === ViewMode.ITEMS_DETAILS;
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
