<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<div id="content"
		class="app-athenaeum">
		<NcAppNavigation>
			<template v-if="!loading"
				#list>
				<NcAppNavigationItem :name="t('athenaeum', 'Sources')"
					:disabled="false"
					:to="'/sources'">
					<template #icon>
						<Bookshelf :size="20" />
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem v-for="folder in folders"
					:key="folder.id"
					:name="t('athenaeum', folder.name)"
					:disabled="false"
					:to="'/items/' + folder.path">
					<template #icon>
						<Inbox v-if="folder.isinbox"
							:size="20" />
						<Bookshelf v-else
							:size="20" />
					</template>
				</NcAppNavigationItem>
			</template>
			<template #footer>
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
				<NcAppNavigationNew :text="t('athenaeum', 'Import EML')"
					button-id="toggle-eml-import-modal"
					@click="showSubmitEMLModal" />
			</template>
		</NcAppNavigation>

		<ItemListView v-if="isItemListView()"
			:key="currentView" />
		<SourceListView v-if="isSourceListView()"
			:key="currentView" />

		<EmlImportModal :visible.sync="emlImportModalVisible"
			@modalClosed="hideSubmitEMLModal" />
	</div>
</template>

<script>
import {
	NcAppNavigation,
	NcAppNavigationItem,
	NcAppNavigationNew,
} from '@nextcloud/vue'

import Bookshelf from 'vue-material-design-icons/Bookshelf.vue'
import Inbox from 'vue-material-design-icons/Inbox.vue'

import ItemListView from './ItemListView.vue'
import SourceListView from './SourceListView.vue'
import EmlImportModal from './EmlImportModal.vue'

import { fetchFolders } from './service/FolderService.js'

import { showError } from '@nextcloud/dialogs'

import { ViewMode } from './enums/index.js'

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
		ItemListView,
		SourceListView,
		EmlImportModal,
	},
	data() {
		return {
			fileItemTrigger: 0,
			ViewMode,
			folders: [],
			loading: false,
			uploading: false,
			emlImportModalVisible: false,
		}
	},
	computed: {
		currentView() {
			return {
				view: this.$route.name,
				folder: this.$route.params.folder,
			}
		},
	},
	async mounted() {
		this.loading = true
		try {
			this.folders = await fetchFolders()
		} catch (e) {
			console.error(e)
			showError(
				t(
					'athenaeum',
					'Could not fetch folders (route mounting failed)',
				),
			)
		}
		this.loading = false
	},
	methods: {
		isItemListView() {
			return (
				this.currentView.view === ViewMode.ITEMS
				|| this.currentView.view === ViewMode.ITEMS_DETAILS
			)
		},
		isSourceListView() {
			return (
				this.currentView.view === ViewMode.SOURCES
				|| this.currentView.view === ViewMode.SOURCES_DETAILS
			)
		},
		showSubmitEMLModal() {
			this.emlImportModalVisible = true
		},
		hideSubmitEMLModal() {
			this.emlImportModalVisible = false
		},
	},
}
</script>
<style lang="scss" scoped>
input[type="text"] {
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
