<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcAppContent>
		<div slot="list"
			class="contributors-list">
			<div id="toptitle">
				<h2>Contributor</h2>
			</div>
			<NcAppContentList class="main-contributors-list"
				:show-details="true">
				<ContributorListItem v-for="contributor in contributors"
					:key="contributor.id"
					:contributor="contributor" />
			</NcAppContentList>

			<div class="contributors-footer">
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
					:disabled="(listOffset + contributors.length) >= totalCount"
					@click="nextPage">
					<template #icon>
						<ChevronRight :size="18" />
					</template>
				</NcButton>
				<NcButton aria-label="Forward multiple pages"
					style="flex:1"
					:disabled="(listOffset + contributors.length) >= totalCount"
					@click="forwardMultiplePages">
					<template #icon>
						<ChevronDoubleRight :size="18" />
					</template>
				</NcButton>
				<NcButton aria-label="Last page"
					style="flex:1"
					:disabled="(listOffset + contributors.length) >= totalCount"
					@click="lastPage">
					<template #icon>
						<PageLast :size="18" />
					</template>
				</NcButton>
			</div>
		</div>
		<ContributorDetails slot="default"
			:contributor-id="currentContributorId" />
	</NcAppContent>
</template>

<script>

import {
	NcAppContent,
	NcAppContentList,
	NcButton,
} from '@nextcloud/vue'

import ContributorListItem from './ContributorListItem.vue'
import ContributorDetails from './ContributorDetails.vue'

import { fetchContributors } from './service/ContributorService.js'
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
	name: 'ContributorListView',
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
		ContributorListItem,
		ContributorDetails,
	},
	data() {
		return {
			contributors: [],
			totalCount: 0,
			listOffset: 0,
			listLimit: 50,
			// currentContributorId: null,
			updating: false,
			loading: true,
		}
	},
	computed: {
		currentFolder() {
			return this.$route.params.folder
		},
		currentContributorId() {
			return parseInt(this.$route.params.contributorId, null)
		},
		currentContributor() {
			if (this.currentContributorId === null) {
				return null
			}
			return this.contributors.find((contributor) => contributor.id === this.currentContributorId)
		},

		saveContributorPossible() {
			return this.currentContributor && this.currentContributor.title !== ''
		},
	},
	async mounted() {
		await this.fetchData()
	},

	methods: {
		async fetchData() {
			this.loading = true
			try {

				const newOffset = this.$route.query.listOffset
					? this.$route.query.listOffset
					: 0
				const contributorData = await fetchContributors(
					newOffset,
				)
				this.contributors = contributorData.contributors
				this.listOffset = contributorData.offset
				this.totalCount = contributorData.totalCount

			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch contributors (route mounting failed)'))
			}
			this.loading = false
		},
		newContributor() {
			if (this.currentContributorId !== -1) {
				this.currentContributorId = -1
				this.contributors.push({
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
		getSubtitle(contributor) {
			const authors = contributor.authors ? contributor.authors : ''
			const journal = contributor.journal ? contributor.journal : ''
			if (authors && journal) {
				return authors + ' - ' + journal
			}
			return authors + journal

		},
		cancelNewContributor() {
			this.contributors.splice(this.contributors.findIndex((contributor) => contributor.id === -1), 1)
			this.currentContributorId = null
		},
		async createContributor(contributor) {
			this.updating = true
			try {
				const response = await axios.post(generateUrl('/apps/athenaeum/res/contributors'), contributor)
				const index = this.contributors.findIndex((match) => match.id === this.currentContributorId)
				this.$set(this.contributors, index, response.data)
				this.currentContributorId = response.data.id
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not create the contributor'))
			}
			this.updating = false
		},
		async updateContributor(contributor) {
			this.updating = true
			try {
				await axios.put(generateUrl(`/apps/athenaeum/res/contributors/${contributor.id}`), contributor)
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not update the contributor'))
			}
			this.updating = false
		},
		async deleteContributor(contributor) {
			try {
				await axios.delete(generateUrl(`/apps/athenaeum/res/contributors/${contributor.id}`))
				this.contributors.splice(this.contributors.indexOf(contributor), 1)
				if (this.currentContributorId === contributor.id) {
					this.currentContributorId = null
				}
				// showSuccess(t('athenaeum', 'Scholar Contributor deleted'))
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not delete the contributor'))
			}
		},
		setNewContributorOffset(newOffset) {
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
			this.setNewContributorOffset(0)
		},
		backMultiplePages() {
			this.setNewContributorOffset(Math.floor((Math.floor(
				this.listOffset / this.listLimit) - 1)
				* 0.5) * this.listLimit)
		},
		prevPage() {
			this.setNewContributorOffset(this.listOffset - this.listLimit)
		},
		nextPage() {
			this.setNewContributorOffset(this.listOffset + this.listLimit)
		},
		forwardMultiplePages() {
			this.setNewContributorOffset(Math.floor((Math.floor(
				this.listOffset / this.listLimit) + 1
				+ Math.floor(this.totalCount / this.listLimit))
				* 0.5) * this.listLimit)
		},
		lastPage() {
			this.setNewContributorOffset(this.totalCount)
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

.contributors-list {
	display: flex;
	flex-direction: column;
	height: 100%;
}

.main-contributors-list {
	min-height: inherit;
	max-height: inherit;
}

.contributors-footer {
	display: flex;
	flex-direction: row;
	width: 100%;
	padding: 0.5em;
}
</style>
