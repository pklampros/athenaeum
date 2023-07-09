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
				<InboxItem v-for="inboxItem in inboxItems"
					:key="inboxItem.id"
					:inboxItem="inboxItem">
				</InboxItem>
			</NcAppContentList>
		</div>
		<InboxItemDetails slot="default"/>
	</NcAppContent>
</template>


<script>

import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent'
import NcAppContentList from '@nextcloud/vue/dist/Components/NcAppContentList'

import InboxItem from './InboxItem.vue'
import InboxItemDetails from './InboxItemDetails.vue'

import { fetchInboxItems } from './service/InboxItemService'

export default {
	name: 'InboxView',
	components: {
		// components
		NcAppContent,
		NcAppContentList,

		// project components
		InboxItem,
		InboxItemDetails,
	},
	data() {
		return {
			inboxItems: [],
			currentInboxItemId: null,
			updating: false,
			loading: true,
		}
	},
	computed: {
		currentInboxItem() {
			if (this.currentInboxItemId === null) {
				return null
			}
			return this.inboxItems.find((inboxItem) => inboxItem.id === this.currentInboxItemId)
		},

		saveInboxItemPossible() {
			return this.currentInboxItem && this.currentInboxItem.title !== ''
		},
	},
	async mounted() {
		try {
			this.inboxItems = await fetchInboxItems()
			console.log(this.inboxItems)
		} catch (e) {
			console.error(e)
			showError(t('athenaeum', 'Could not fetch items (route mounting failed)'))
		}
		this.loading = false
	},

	methods: {

		newInboxItem() {
			if (this.currentInboxItemId !== -1) {
				this.currentInboxItemId = -1
				this.inboxItems.push({
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
		getSubtitle(inboxItem) {
			const authors = inboxItem.authors ? inboxItem.authors : ''
			const journal = inboxItem.journal ? inboxItem.journal : ''
			if (authors && journal) {
				return authors + ' - ' + journal
			}
			return authors + journal
		
		},
		cancelNewInboxItem() {
			this.inboxItems.splice(this.inboxItems.findIndex((inboxItem) => inboxItem.id === -1), 1)
			this.currentInboxItemId = null
		},
		async createInboxItem(inboxItem) {
			this.updating = true
			try {
				const response = await axios.post(generateUrl('/apps/athenaeum/inbox_items'), inboxItem)
				const index = this.inboxItems.findIndex((match) => match.id === this.currentInboxItemId)
				this.$set(this.inboxItems, index, response.data)
				this.currentInboxItemId = response.data.id
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not create the inbox item'))
			}
			this.updating = false
		},
		async updateInboxItem(inboxItem) {
			this.updating = true
			try {
				await axios.put(generateUrl(`/apps/athenaeum/inbox_items/${inboxItem.id}`), inboxItem)
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not update the inbox item'))
			}
			this.updating = false
		},
		async deleteInboxItem(inboxItem) {
			try {
				await axios.delete(generateUrl(`/apps/athenaeum/inbox_items/${inboxItem.id}`))
				this.inboxItems.splice(this.inboxItems.indexOf(inboxItem), 1)
				if (this.currentInboxItemId === inboxItem.id) {
					this.currentInboxItemId = null
				}
				showSuccess(t('athenaeum', 'Scholar Item deleted'))
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not delete the inbox item'))
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
