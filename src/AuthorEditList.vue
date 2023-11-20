<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<div id="author-edit-list">
		<div class="field-label">
			<h3>Authors</h3>
			<NcButton aria-label="Add"
				type="tertiary"
				@click="addAuthor()">
				<template #icon>
					<PlusCircle :size="20" />
				</template>
			</NcButton>
		</div>
		<ul style="list-style: inherit; padding: 4px 0 4px 44px;">
			<li v-for="(author, index) in authorList"
				:key="author">
				<div class="flex-row">
					<div>
						<div class="first-row">
							<div v-if="!author.existingContributor">
								<div v-if="!author.onlyLastName" class="flex-row">
									<NcTextField label="First"
										:error="emptyOrHasEllipsis(author.firstName)"
										:value.sync="author.firstName"
										@update:value="updateDisplayName(index)" />
									&nbsp;
									<NcTextField :label="'Last'"
										:error="emptyOrHasEllipsis(author.name)"
										:value.sync="author.name"
										@update:value="updateDisplayName(index)" />
								</div>
								<NcTextField v-else
									:label="'Name'"
									:error="emptyOrHasEllipsis(author.name)"
									:value.sync="author.name"
									@update:value="updateDisplayName(index)" />
							</div>
							<div v-else>
								{{ author.existingContributor.firstName + " " + author.existingContributor.lastName }}
							</div>
						</div>
						<div class="flex-row last-row">
							<label for="displayNameField">Displayed&nbsp;as&nbsp;&nbsp;</label>
							<NcTextField id="displayNameField"
								:error="emptyOrHasEllipsis(author.displayName)"
								:label-outside="true"
								:value.sync="author.displayName"
								@update:value="displayNameSet(index)" />
						</div>
					</div>
					<NcButton v-if="author.onlyLastName"
						aria-label="Single-field name?"
						type="tertiary"
						@click="toggleOnlyLastName(index)">
						<template #icon>
							<TextBox :size="20" />
							<!--
								Other potential pairs:
								- CommentOutline / CommentMultipleOutline
								- LayersOutline / LayersTripleOutline
								- Locker / LockerMultiple
								- PencilBox / PencilBoxMultiple
								- TextBoxOutline / SelectCompare
								- TextBoxOutline / TextBoxMultipleOutline
							-->
						</template>
					</NcButton>
					<NcButton v-else
						aria-label="Single-field name?"
						type="tertiary"
						@click="toggleOnlyLastName(index)">
						<template #icon>
							<TextBoxMultiple :size="20" />
						</template>
					</NcButton>
					<NcButton v-if="index == 0"
						aria-label="Move down"
						type="tertiary"
						@click="moveDown(index)">
						<template #icon>
							<ChevronDown :size="20" />
						</template>
					</NcButton>
					<NcButton v-else
						aria-label="Move up"
						type="tertiary"
						@click="moveUp(index)">
						<template #icon>
							<ChevronUp :size="20" />
						</template>
					</NcButton>
					<div style="flex: 1; display:flex">
						<NcCheckboxRadioSwitch :button-variant="true"
							:checked.sync="author.isNew"
							:value="false"
							:name="'existing_new_radio_' + index"
							type="radio"
							button-variant-grouped="horizontal"
							@update:checked="findSimilarContributors(index)">
							<Magnify v-if="!author.existingContributor" :size="20" />
							<DatabaseCheck v-else :size="20" />
							<!-- when  there is a suggested already from the database: <DatabaseSearch :size="20" /> -->
							<NcPopover :shown="author.potentialContributors.popoverVisible"
								container="#author-edit-list"
								popover-base-class="similar-contributors_pop"
								@apply-hide="dismissPotentialContributors(index)">
								<template #default>
									<div>
										<h2>Similar contributors</h2>
										<div v-if="author.potentialContributors.error" style="padding: 1em">
											<span>
												{{ author.potentialContributors.error }}<br>
											</span>
										</div>
										<ul v-else>
											<NcListItem v-for="contributor in author.potentialContributors.found"
												:key="contributor.id"
												:title="contributor.firstName + ' ' + contributor.lastName"
												@click="selectContributor(index, contributor)">
												<div slot="subtitle">
													{{ contributor.displayName }}
												</div>
											</NcListItem>
										</ul>
										<NcButton style="width: 100%;"
											aria-label="Advanced search"
											@click="advancedSearch(index)">
											Advanced search
										</NcButton>
									</div>
								</template>
							</NcPopover>
						</NcCheckboxRadioSwitch>
						<NcCheckboxRadioSwitch :button-variant="true"
							:checked.sync="author.isNew"
							:value="true"
							:name="'existing_new_radio_' + index"
							type="radio"
							button-variant-grouped="horizontal"
							@update:checked="markAsNew(index)">
							New
						</NcCheckboxRadioSwitch>
					</div>
					<NcButton aria-label="Remove"
						type="tertiary"
						@click="removeAuthor(index)">
						<template #icon>
							<MinusCircle :size="20" />
						</template>
					</NcButton>
				</div>
			</li>
		</ul>
		<SimilarAuthorsModal :contributor-search-term.sync="contributorSearchTerm"
			:author-index="modalAuthorIndex"
			@selectContributor="selectContributor" />
	</div>
</template>
<script>
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcListItem from '@nextcloud/vue/dist/Components/NcListItem.js'
import NcPopover from '@nextcloud/vue/dist/Components/NcPopover.js'

import Magnify from 'vue-material-design-icons/Magnify.vue'
import TextBox from 'vue-material-design-icons/TextBox.vue'
import TextBoxMultiple from 'vue-material-design-icons/TextBoxMultiple.vue'
import ChevronUp from 'vue-material-design-icons/ChevronUp.vue'
import ChevronDown from 'vue-material-design-icons/ChevronDown.vue'
import MinusCircle from 'vue-material-design-icons/MinusCircle.vue'
import PlusCircle from 'vue-material-design-icons/PlusCircle.vue'
import DatabaseCheck from 'vue-material-design-icons/DatabaseCheck.vue'

import SimilarAuthorsModal from './SimilarAuthorsModal.vue'

import { findSimilar } from './service/ContributorService.js'
import { authorMxn } from './mixins/authors.js'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'AuthorEditList',
	components: {
		// components
		NcTextField,
		NcCheckboxRadioSwitch,
		NcButton,
		NcListItem,
		NcPopover,

		// icons
		Magnify,
		TextBox,
		TextBoxMultiple,
		ChevronUp,
		ChevronDown,
		MinusCircle,
		PlusCircle,
		DatabaseCheck,

		// project components
		SimilarAuthorsModal,
	},
	mixins: [authorMxn],
	data() {
		return {
			authorList: null,
			contributorSearchTerm: null,
			modalAuthorIndex: null,
		}
	},
	watch: {
		authorList(newAuthorList) {
			this.$emit('author-list-updated', newAuthorList)
		},
	},
	mounted() {
		this.emitInterface()
	},
	methods: {
		setAuthorList(authors) {
			this.authorList = authorMxn.getContributorList(authors, true)
		},
		setAuthorListFromText(text) {
			this.authorList = authorMxn.getContributorListFromTxt(text)
		},
		markInboxItemDeleted() {
			this.inboxItemBeingFiled = null
			this.authorList = null
		},
		updateDisplayName(authorIndex) {
			const author = this.authorList[authorIndex]
			if (author.displayNameModified) return
			let nameComponents = 0
			if (author.name) nameComponents = nameComponents + 1
			if (author.firstName) nameComponents = nameComponents + 1

			author.displayName = author.firstName.trim()
								 + (nameComponents === 2 ? ' ' : '')
								 + author.name.trim()
			this.$set(this.authorList, authorIndex, author)
		},
		displayNameSet(authorIndex) {
			const author = this.authorList[authorIndex]
			author.displayNameModified = (author.displayName !== '')
			this.$set(this.authorList, authorIndex, author)
		},
		toggleOnlyLastName(authorIndex) {
			let author = this.authorList[authorIndex]
			if (!author.onlyLastName) {
				author.onlyLastName = true
			} else {
				author.onlyLastName = !author.onlyLastName
			}
			if (author.onlyLastName && author.firstName) {
				author.name = author.firstName.trim() + ' ' + author.name.trim()
				author.firstName = ''
			}
			if (!author.onlyLastName && author.firstName === '') {
				const authorMarkedNew = author.isNew
				author = authorMxn.getAuthorNameData(author.name)
				author.isNew = authorMarkedNew
				author.onlyLastName = false
			}
			this.$set(this.authorList, authorIndex, author)
		},
		moveDown(authorIndex) {
			if (authorIndex < 0 || authorIndex > this.authorList.length - 1) return
			this.$set(this.authorList, authorIndex,
					  this.authorList.splice(authorIndex + 1, 1,
											 this.authorList[authorIndex])[0])
		},
		moveUp(authorIndex) {
			if (authorIndex < 1 || authorIndex > this.authorList.length) return
			this.$set(this.authorList, authorIndex,
					  this.authorList.splice(authorIndex - 1, 1,
											 this.authorList[authorIndex])[0])
		},
		removeAuthor(authorIndex) {
			if (authorIndex < 0 || authorIndex > this.authorList.length) return
			this.authorList.splice(authorIndex, 1)
		},
		async findSimilarContributors(authorIndex) {
			const author = this.authorList[authorIndex]
			author.potentialContributors.popoverVisible = true
			author.potentialContributors.loading = true
			author.potentialContributors.error = ''
			this.$set(this.authorList, authorIndex, author)
			try {
				author.potentialContributors.found = await findSimilar(author.firstName, author.name, author.displayName)
				author.potentialContributors.loading = false
				author.potentialContributors.error = author.potentialContributors.found.length === 0 ? 'None Found...' : ''
				this.$set(this.authorList, authorIndex, author)
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch items (route mounting failed)'))
			}
		},
		dismissPotentialContributors(authorIndex) {
			const author = this.authorList[authorIndex]
			author.potentialContributors.found = []
			author.potentialContributors.popoverVisible = false
			author.potentialContributors.error = ''
			author.isNew = author.existingContributor === null
			this.$set(this.authorList, authorIndex, author)
		},
		selectContributor(authorIndex, contributorData) {
			const author = this.authorList[authorIndex]
			author.existingContributor = contributorData
			this.$set(this.authorList, authorIndex, author)
			this.dismissPotentialContributors(authorIndex)
			this.contributorSearchTerm = null
		},
		markAsNew(authorIndex) {
			const author = this.authorList[authorIndex]
			author.existingContributor = null
			author.isNew = true
			this.$set(this.authorList, authorIndex, author)
		},
		addAuthor() {
			const authorData = authorMxn.getAuthorNameData('')
			authorData.onlyLastName = false
			authorData.isNew = true
			authorData.existingContributor = null
			this.authorList.push(authorData)
		},
		emitInterface() {
			this.$emit('interface', {
				setAuthorList: (authors) => this.setAuthorList(authors),
				setAuthorListFromText: (authors) => this.setAuthorListFromText(authors),
			})
		},
		hasEllipsis(text) {
			return text.includes('…') || text.includes('...')
		},
		emptyOrHasEllipsis(text) {
			return !text || text === '' || this.hasEllipsis(text)
		},
		advancedSearch(authorIndex) {
			const author = this.authorList[authorIndex]
			this.contributorSearchTerm = author.name
			this.modalAuthorIndex = authorIndex
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

	.flex-row {
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.flex-row label {
		margin: 8px 0px;
	}

	.first-row .input-field, .first-row label  {
		margin-bottom: 2px;
	}

	.last-row .input-field, .last-row label {
		margin-top: 2px;
	}

	:deep(.checkbox-radio-switch__label) {
		min-height: 0px;
	}

	:deep(.field-label) {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 10px 1px 0px 0px;
	}

	:deep(.field-label h3) {
		font-weight: bold;
		margin: 8px 0px 8px 12px;
		text-align: start;
	}
</style>

<style lang="scss">
	.v-popper--theme-dropdown.v-popper__popper.similar-contributors_pop .v-popper__wrapper {
		border-radius: var(--border-radius-large);
		.v-popper__inner {
			padding: 10px;
			border-radius: var(--border-radius-large);

			.flex-row {
				display: flex;
				justify-content: center;
				align-items: center;
			}
		}
	}
</style>
