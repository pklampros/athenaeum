<template>
	<NcModal v-if="contributorSearchTerm !== null">
		<div class="modal__content">
			<h2>Advanced contributor search</h2>
			<div style="display:flex; justify-content: center;">
				<div style="width:44px; height:44px" />
				<NcTextField label="SearchTerm"
					:value="contributorSearchTerm" />
				<NcButton aria-label="Search"
					type="tertiary"
					@click="contributorSearch()">
					<template #icon>
						<Magnify :size="20" />
					</template>
				</NcButton>
			</div>
			<h2>Search results</h2>
			<div v-if="searchError" style="padding: 1em">
				<span>
					{{ searchError }}<br>
				</span>
			</div>
			<ul v-else>
				<NcListItem v-for="contributor in foundContributors"
					:key="contributor.id"
					:title="contributor.firstName + ' ' + contributor.lastName"
					@click="selectContributor(contributor)">
					<div slot="subtitle">
						{{ contributor.displayName }}
					</div>
				</NcListItem>
			</ul>
		</div>
	</NcModal>
</template>
<script>

import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcListItem from '@nextcloud/vue/dist/Components/NcListItem.js'

import Magnify from 'vue-material-design-icons/Magnify.vue'

import { freeSearch } from './service/ContributorService.js'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'SimilarAuthorsModal',
	components: {
		// components
		NcModal,
		NcTextField,
		NcButton,
		NcListItem,

		// icons
		Magnify,
	},
	props: {
		contributorSearchTerm: {
			type: String,
			required: true,
		},
		authorIndex: {
			type: Number,
			required: true,
		},
	},
	data() {
		return {
			foundContributors: [],
			searchError: '',
			loading: false,
		}
	},
	methods: {
		async contributorSearch() {
			if (this.contributorSearchTerm === null || !(this.contributorSearchTerm.trim())) {
				this.foundContributors = []
				this.searchError = 'Search string is empty...'
				return
			}
			this.loading = true
			try {
				this.foundContributors = await freeSearch(this.contributorSearchTerm)
				this.loading = false
				this.searchError = this.foundContributors.length === 0 ? 'None found...' : ''
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch items (route mounting failed)'))
			}
		},
		selectContributor(contributorData) {
			this.$emit('select-contributor', this.authorIndex, contributorData)

			this.foundContributors = []
			this.searchError = ''
			this.loading = false
		},
	},
}
</script>

<style lang="scss" scoped>
	.modal__content {
		margin: 50px;
		text-align: center;
	}

	.input-field {
		margin: 8px 0px;
	}

</style>
