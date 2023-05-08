<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<div id="author-edit-list">
		<div class="field-label">
			<h3>Authors</h3>
			<NcButton
				ariaLabel="Add"
				type="tertiary"
				@click="addAuthor()">
				<template #icon>
					<PlusCircle :size="20" />
				</template>
			</NcButton>
		</div>
		<ul style="list-style: inherit; padding: 4px 0 4px 44px;">
			<li
				v-for="(author, index) in authorList"
				:key="author">
				<div class="flex-row">
					<div>
						<div class="first-row">
							<div v-if="!author.existingContributor">
								<div v-if="!author.onlyLastName" class="flex-row">
									<NcTextField
										label="First"
										:error="emptyOrHasEllipsis(author.firstName)"
										:value.sync="author.firstName"
										@update:value="updateDisplayName(index)" />
									&nbsp;
									<NcTextField
										:label="'Last'"
										:error="emptyOrHasEllipsis(author.name)"
										:value.sync="author.name"
										@update:value="updateDisplayName(index)" />
								</div>
								<NcTextField
									v-else
									:label="'Name'"
										:error="emptyOrHasEllipsis(author.name)"
									:value.sync="author.name"
									@update:value="updateDisplayName(index)" />
							</div>
							<div v-else> {{ author.existingContributor.firstName + " " + author.existingContributor.lastName }}</div>
						</div>
						<div class="flex-row last-row">
							<label for="displayNameField">Displayed&nbsp;as&nbsp;&nbsp;</label>
							<NcTextField
								id="displayNameField"
									:error="emptyOrHasEllipsis(author.displayName)"
								:label-outside="true"
								:value.sync="author.displayName"
								@update:value="displayNameSet(index)">
							</NcTextField>
						</div>
					</div>
					<NcButton
						v-if="author.onlyLastName"
						ariaLabel="Single-field name?"
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
					<NcButton
						v-else
						ariaLabel="Single-field name?"
						type="tertiary"
						@click="toggleOnlyLastName(index)">
						<template #icon>
							<TextBoxMultiple :size="20" />
						</template>
					</NcButton>
					<NcButton
						v-if="index == 0"
						ariaLabel="Move down"
						type="tertiary"
						@click="moveDown(index)">
						<template #icon>
							<ChevronDown :size="20" />
						</template>
					</NcButton>
					<NcButton
						v-else
						ariaLabel="Move up"
						type="tertiary"
						@click="moveUp(index)">
						<template #icon>
							<ChevronUp :size="20" />
						</template>
					</NcButton>
					<div style="flex: 1; display:flex">
						<NcCheckboxRadioSwitch
							:button-variant="true"
							:checked.sync="author.isNew"
							:value="false"
							:name="'existing_new_radio_' + index"
							type="radio"
							button-variant-grouped="horizontal"
							@update:checked="findSimilarContributors(index)">
							<Magnify :size="20" v-if="!author.existingContributor"/>
							<DatabaseCheck :size="20" v-else />
							<!-- when  there is a suggested already from the database: <DatabaseSearch :size="20" /> -->
							<NcPopover
								:shown="author.hasPotentialContributors()"
								container="#author-edit-list"
								popover-base-class="similar-contributors_pop"
								@apply-hide="dismissPotentialContributors(index)">
								<template>
									<div>
										<h2>Similar contributors</h2>
										<ul>
											<NcListItem
												v-for="contributor in author.potentialContributors"
												:key="contributor.id"
												:title="contributor.firstName + ' ' + contributor.lastName"
												@click="selectContributor(index, contributor)">
												<div slot="subtitle">
													{{ contributor.displayName }}
												</div>
											</NcListItem>
										</ul>
										<NcButton style="width: 100%;" aria-label="Advanced search">Advanced search</NcButton>
									</div>
								</template>
							</NcPopover>
						</NcCheckboxRadioSwitch>
						<NcCheckboxRadioSwitch
							:button-variant="true"
							:checked.sync="author.isNew"
							:value="true"
							:name="'existing_new_radio_' + index"
							type="radio"
							button-variant-grouped="horizontal"
							@update:checked="markAsNew(index)">
							New
						</NcCheckboxRadioSwitch>
					</div>
					<NcButton
						ariaLabel="Remove"
						type="tertiary"
						@click="removeAuthor(index)">
						<template #icon>
							<MinusCircle :size="20" />
						</template>
					</NcButton>
				</div>
			</li>
		</ul>
	</div>
</template>
<script>
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch'
import NcButton from '@nextcloud/vue/dist/Components/NcButton'
import NcListItem from '@nextcloud/vue/dist/Components/NcListItem'
import NcPopover from '@nextcloud/vue/dist/Components/NcPopover'

import Magnify from 'vue-material-design-icons/Magnify.vue';
import TextBox from 'vue-material-design-icons/TextBox.vue';
import TextBoxMultiple from 'vue-material-design-icons/TextBoxMultiple.vue';
import ChevronUp from 'vue-material-design-icons/ChevronUp.vue';
import ChevronDown from 'vue-material-design-icons/ChevronDown.vue';
import MinusCircle from 'vue-material-design-icons/MinusCircle.vue';
import PlusCircle from 'vue-material-design-icons/PlusCircle.vue';
import DatabaseCheck from 'vue-material-design-icons/DatabaseCheck.vue';

import { findSimilar } from './service/ContributorService'

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
	},
    mounted() {
		this.emitInterface();
    },
	data() {
		return {
			authorList: null,
		}
	},
	watch: {
		authorList: function(newAuthorList) {
			this.$emit("authorListUpdated", newAuthorList);
		}
	},
	methods: {
		setAuthorListFromText(authors) {
			this.authorList = authors.split(",");
			let hasMoreAuthors = false;
			this.authorList = this.authorList.map((author) => {
				if (author.endsWith("…")) {
					author = author.slice(0, -1);
					hasMoreAuthors = true;
				}
				author = author.trim()
				let authorData = this.getAuthorNameData(author)
				authorData.isNew = true;
				return authorData;
			});
			if (hasMoreAuthors) {
				this.authorList.push({
					"name": "…",
					"firstName": "…",
					"displayName": "…",
					"displayNameModified": false,
					"onlyLastName": true,
					"isNew": true,
					"potentialContributors": [],
					"hasPotentialContributors": function() {
						return this.potentialContributors.length > 0;
					},
					"existingContributor": null,
				})
			}
		},
		getAuthorNameData(author) {
			let authorNameParts = author.trim().split(" ");
			let name = authorNameParts.at(-1).trim()
			let nameData = {
				"name": name,
				"firstName": "",
				"displayName": author,
				"displayNameModified": authorNameParts > 1 && name != "",
				"onlyLastName": authorNameParts > 1,
				"potentialContributors": [],
				"hasPotentialContributors": function() {
					return this.potentialContributors.length > 0;
				},
				"existingContributor": null,
			}
			if (authorNameParts.length > 1) {
				nameData.firstName = authorNameParts.slice(0, -1).join(' ').trim();
				nameData.onlyLastName = false;
			}
			return nameData;
		},
		markScholarItemDeleted() {
			this.scholarItemBeingFiled = null
			this.authorList = null
		},
		updateDisplayName(authorIndex) {
			let author = this.authorList[authorIndex];
			if (author.displayNameModified) return;
			let nameComponents = 0;
			if (author.name) nameComponents = nameComponents + 1;
			if (author.firstName) nameComponents = nameComponents + 1;

			author.displayName = author.firstName.trim() +
								 (nameComponents == 2 ? ' ' : '') +
								 author.name.trim();
			this.$set(this.authorList, authorIndex, author);
		},
		displayNameSet(authorIndex) {
			let author = this.authorList[authorIndex];
			author.displayNameModified = (author.displayName != '')
			this.$set(this.authorList, authorIndex, author);
		},
		toggleOnlyLastName(authorIndex) {
			let author = this.authorList[authorIndex]
			if (!author.onlyLastName) {
				author.onlyLastName = true
			} else {
				author.onlyLastName = !author.onlyLastName
			}
			if (author.onlyLastName && author.firstName) {
				author.name = author.firstName.trim() + ' ' + author.name.trim();
				author.firstName = "";
			}
			if (!author.onlyLastName && author.firstName == "") {
				let authorMarkedNew = author.isNew;
				author = this.getAuthorNameData(author.name);
				author.isNew = authorMarkedNew;
				author.onlyLastName = false;
			}
			this.$set(this.authorList, authorIndex, author);
		},
		moveDown(authorIndex) {
			if (authorIndex < 0 || authorIndex > this.authorList.length - 1) return;
			this.$set(this.authorList, authorIndex,
					  this.authorList.splice(authorIndex+1, 1,
											 this.authorList[authorIndex])[0]);
		},
		moveUp(authorIndex) {
			if (authorIndex < 1 || authorIndex > this.authorList.length) return;
			this.$set(this.authorList, authorIndex,
					  this.authorList.splice(authorIndex-1, 1,
											 this.authorList[authorIndex])[0]);
		},
		removeAuthor(authorIndex) {
			if (authorIndex < 0 || authorIndex > this.authorList.length) return;
			this.authorList.splice(authorIndex, 1);
		},
		async findSimilarContributors(authorIndex) {
			let author = this.authorList[authorIndex];
			try {
				author.potentialContributors = await findSimilar(author.firstName, author.name, author.displayName)
				this.$set(this.authorList, authorIndex, author);
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch items (route mounting failed)'))
			}
		},
		dismissPotentialContributors(authorIndex) {
			let author = this.authorList[authorIndex];
			author.potentialContributors = [];
			author.isNew = author.existingContributor === null
			this.$set(this.authorList, authorIndex, author);
		},
		selectContributor(authorIndex, contributorData) {
			let author = this.authorList[authorIndex];
			author.existingContributor = contributorData;
			this.$set(this.authorList, authorIndex, author);
			this.dismissPotentialContributors(authorIndex);
		},
		markAsNew(authorIndex) {
			let author = this.authorList[authorIndex];
			author.existingContributor = null;
			author.isNew = true;
			this.$set(this.authorList, authorIndex, author);
		},
		hasPotentialContributors() {
			if (!this.potentialContributors) return false;
			return this.potentialContributors.length > 0;
		},
		addAuthor() {
			let authorData = this.getAuthorNameData("")
			authorData.onlyLastName = false;
			authorData.isNew = true;
			authorData.existingContributor = null;
			this.authorList.push(authorData);
		},
		emitInterface() {
			this.$emit("interface", {
				setAuthorListFromText: (authors) => this.setAuthorListFromText(authors)
			});
		},
		hasEllipsis(text) {
			return text.includes('…') || text.includes("...")
		},
		emptyOrHasEllipsis(text) {
			return !text || text == "" || this.hasEllipsis(text);
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