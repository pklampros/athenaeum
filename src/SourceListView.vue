<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcAppContent>
		<div slot="list"
			class="sources-list">
			<div id="toptitle">
				<h2>Source</h2>
			</div>
			<NcAppContentList class="main-sources-list"
				:show-details="true">
				<SourceListItem v-for="source in sources"
					:key="source.id"
					:source="source" />
			</NcAppContentList>

			<div class="sources-footer">
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
					:disabled="(listOffset + sources.length) >= totalCount"
					@click="nextPage">
					<template #icon>
						<ChevronRight :size="18" />
					</template>
				</NcButton>
				<NcButton aria-label="Forward multiple pages"
					style="flex:1"
					:disabled="(listOffset + sources.length) >= totalCount"
					@click="forwardMultiplePages">
					<template #icon>
						<ChevronDoubleRight :size="18" />
					</template>
				</NcButton>
				<NcButton aria-label="Last page"
					style="flex:1"
					:disabled="(listOffset + sources.length) >= totalCount"
					@click="lastPage">
					<template #icon>
						<PageLast :size="18" />
					</template>
				</NcButton>
			</div>
		</div>
		<SourceDetails slot="default"
			:source-id="currentSourceId" />
	</NcAppContent>
</template>

<script>

import {
	NcAppContent,
	NcAppContentList,
	NcButton,
} from '@nextcloud/vue'

import SourceListItem from './SourceListItem.vue'
import SourceDetails from './SourceDetails.vue'

import { fetchSources } from './service/SourceService.js'
import { showError } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue'
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue'
import ChevronDoubleLeft from 'vue-material-design-icons/ChevronDoubleLeft.vue'
import ChevronDoubleRight from 'vue-material-design-icons/ChevronDoubleRight.vue'
import PageFirst from 'vue-material-design-icons/PageFirst.vue'
import PageLast from 'vue-material-design-icons/PageLast.vue'

export default {
	name: 'SourceListView',
	components: {
		// components
		NcAppContent,
		NcAppContentList,
		NcButton,

		// icons
		ChevronLeft,
		ChevronRight,
		ChevronDoubleLeft,
		ChevronDoubleRight,
		PageFirst,
		PageLast,

		// project components
		SourceListItem,
		SourceDetails,
	},
	data() {
		return {
			sources: [],
			totalCount: 0,
			listOffset: 0,
			listLimit: 50,
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
		setNewSourceOffset(newOffset) {
			if (newOffset < 0) {
				newOffset = 0
			}
			if (newOffset >= this.totalCount) {
				newOffset = this.listLimit
					* Math.floor(this.totalCount / this.listLimit)
			}
			this.$router.replace({ query: { listOffset: newOffset } })
			this.fetchData()
		},
		firstPage() {
			this.setNewSourceOffset(0)
		},
		backMultiplePages() {
			this.setNewSourceOffset(Math.floor((Math.floor(
				this.listOffset / this.listLimit) - 1)
				* 0.5) * this.listLimit)
		},
		prevPage() {
			this.setNewSourceOffset(this.listOffset - this.listLimit)
		},
		nextPage() {
			this.setNewSourceOffset(this.listOffset + this.listLimit)
		},
		forwardMultiplePages() {
			this.setNewSourceOffset(Math.floor((Math.floor(
				this.listOffset / this.listLimit) + 1
				+ Math.floor(this.totalCount / this.listLimit))
				* 0.5) * this.listLimit)
		},
		lastPage() {
			this.setNewSourceOffset(this.totalCount)
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

.sources-list {
	display: flex;
	flex-direction: column;
	height: 100%;
}

.main-sources-list {
	min-height: inherit;
	max-height: inherit;
}

.sources-footer {
	display: flex;
	flex-direction: row;
	width: 100%;
	padding: 0.5em;
}
</style>
