<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcAppContent>
		<div slot="list" class="header__button">
			<div id="toptitle">
				<h2>Source</h2>
			</div>
			<NcAppContentList class="main-items-list"
				:show-details="true">
				<SourceListItem v-for="source in sources"
					:key="source.id"
					:source="source" />
			</NcAppContentList>
		</div>
		<SourceDetails slot="default" :source-id="currentSourceId" />
	</NcAppContent>
</template>

<script>

import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'
import NcAppContentList from '@nextcloud/vue/dist/Components/NcAppContentList.js'

import SourceListItem from './SourceListItem.vue'
import SourceDetails from './SourceDetails.vue'

import { fetchSources } from './service/SourceService.js'
import { showError } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
	name: 'SourceView',
	components: {
		// components
		NcAppContent,
		NcAppContentList,

		// project components
		SourceListItem,
		SourceDetails,
	},
	data() {
		return {
			sources: [],
			// currentSourceId: null,
			updating: false,
			loading: true,
		}
	},
	computed: {
		currentFolder() {
			return this.$route.params.folder
		},
		currentSourceId() {
			return parseInt(this.$route.params.sourceId, null)
		},
		currentSource() {
			if (this.currentSourceId === null) {
				return null
			}
			return this.sources.find((source) => source.id === this.currentSourceId)
		},

		saveSourcePossible() {
			return this.currentSource && this.currentSource.title !== ''
		},
	},
	async mounted() {
		try {
			this.sources = await fetchSources(this.currentFolder)
		} catch (e) {
			console.error(e)
			showError(t('athenaeum', 'Could not fetch sources (route mounting failed)'))
		}
		this.loading = false
	},

	methods: {
		newSource() {
			if (this.currentSourceId !== -1) {
				this.currentSourceId = -1
				this.sources.push({
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
		getSubtitle(source) {
			const authors = source.authors ? source.authors : ''
			const journal = source.journal ? source.journal : ''
			if (authors && journal) {
				return authors + ' - ' + journal
			}
			return authors + journal

		},
		cancelNewSource() {
			this.sources.splice(this.sources.findIndex((source) => source.id === -1), 1)
			this.currentSourceId = null
		},
		async createSource(source) {
			this.updating = true
			try {
				const response = await axios.post(generateUrl('/apps/athenaeum/res/sources'), source)
				const index = this.sources.findIndex((match) => match.id === this.currentSourceId)
				this.$set(this.sources, index, response.data)
				this.currentSourceId = response.data.id
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not create the source'))
			}
			this.updating = false
		},
		async updateSource(source) {
			this.updating = true
			try {
				await axios.put(generateUrl(`/apps/athenaeum/res/sources/${source.id}`), source)
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not update the source'))
			}
			this.updating = false
		},
		async deleteSource(source) {
			try {
				await axios.delete(generateUrl(`/apps/athenaeum/res/sources/${source.id}`))
				this.sources.splice(this.sources.indexOf(source), 1)
				if (this.currentSourceId === source.id) {
					this.currentSourceId = null
				}
				// showSuccess(t('athenaeum', 'Scholar Source deleted'))
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not delete the source'))
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
