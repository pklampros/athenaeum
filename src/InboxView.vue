<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcAppContent>
		<div slot="list" class='header__button'>
			<div id="toptitle">
				<h2 >Inbox</h2>
			</div>
			<NcAppContentList
				class="main-items-list"
				:show-details="true">
				<ScholarItem v-for="scholarItem in scholarItems"
					:key="scholarItem.id"
					:scholarItem="scholarItem">
				</ScholarItem>
			</NcAppContentList>
		</div>
		<ScholarItemDetails slot="default"/>
	</NcAppContent>
</template>


<script>

import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent'
import NcAppContentList from '@nextcloud/vue/dist/Components/NcAppContentList'

import ScholarItem from './ScholarItem.vue'
import ScholarItemDetails from './ScholarItemDetails.vue'

import { fetchScholarItems } from './service/ScholarItemService'


export default {
	name: 'InboxView',
	components: {
		// components
		NcAppContent,
		NcAppContentList,

		// project components
		ScholarItem,
		ScholarItemDetails,
	},
	data() {
		return {
			scholarItems: [],
			currentScholarItemId: null,
			updating: false,
			loading: true,
		}
	},
	computed: {
		currentScholarItem() {
			if (this.currentScholarItemId === null) {
				return null
			}
			return this.scholarItems.find((scholarItem) => scholarItem.id === this.currentScholarItemId)
		},

		saveScholarItemPossible() {
			return this.currentScholarItem && this.currentScholarItem.title !== ''
		},
	},
	async mounted() {
		try {
			this.scholarItems = await fetchScholarItems()
		} catch (e) {
			console.error(e)
			showError(t('athenaeum', 'Could not fetch items (route mounting failed)'))
		}
		this.loading = false
	},

	methods: {

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
		cancelNewScholarItem() {
			this.scholarItems.splice(this.scholarItems.findIndex((scholarItem) => scholarItem.id === -1), 1)
			this.currentScholarItemId = null
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
