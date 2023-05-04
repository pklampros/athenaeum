<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcAppContentDetails v-if="scholarItem">
		<div style="max-width: 900px; margin: 0 auto;">
			<div style="position: sticky; padding: 30px 18px;">
				<h2
					:title="scholarItem.title"
					style="display: flex; align-items: center; justify-content: space-between;">
					{{ scholarItem.title }}
					<a :href="scholarItem.url" target="_blank"><OpenInNew></a>
				</h2>
				<h3> {{ scholarItem.journal }} </h3>
				<h3> {{ authorListDisplay }} </h3>
				&nbsp;
				<h3 style="font-weight: bold;">Excerpts:</h3>
				<ul style="list-style: inherit; padding: 4px 0 4px 44px;">
					<li v-for="alertExcerpt in alertExcerpts"
								:key="alertExcerpt">
						<div>
							<span style="color: var(--color-main-text);font-weight: bold;">
								{{ alertExcerpt.excerpt }}
							</span>
							<div style="padding: 5px 0px;">
								<span style="color: var(--color-text-maxcontrast);">
									Search term: {{ alertExcerpt.term }}
								</span>
							</div>
						</div>
					</li>
				</ul>
			</div>
			<div style="padding:0px 10px; border-radius: 16px; border: 2px solid var(--color-border);">
				<div class="field-label">
					<h3>Title</h3>
				</div>
				<NcRichContenteditable
					placeholder="Title"
					:error="hasEllipsis(scholarItem.title)"
					:value.sync="scholarItem.title" />
				<div class="field-label">
					<h3>URL</h3>
				</div>
				<NcRichContenteditable
					placeholder="URL"
					:value.sync="scholarItem.url" />
				<div class="field-label">
					<h3>Journal</h3>
				</div>
				<NcRichContenteditable
					placeholder="Journal"
					:error="hasEllipsis(scholarItem.journal)"
					:value.sync="scholarItem.journal"/>
				<AuthorEditList
					@interface="setAuthorListInterface"
					@authorListUpdated="authorListUpdated"/>
				&nbsp;
			</div>
			<div style="display: flex; justify-content: right; align-items: center; padding: 16px;">
				<NcButton
					ariaLabel="Remove scholar item"
					:disabled="!this.scholarItem.title || !this.scholarItem.url"
					@click="markScholarItemDeleted"
					type="primary">
					<template #icon>
						<Delete :size="20" />
					</template>
				</NcButton>
				&nbsp;
				<NcButton
					:disabled="!this.scholarItem.title || !this.scholarItem.url"
					@click="addToLibrary"
					type="primary">
					Add to Library
				</NcButton>
			</div>
		</div>
	</NcAppContentDetails>
	<NcAppContentDetails v-else>
		<NcEmptyContent
			:title="t('athenaeum', 'No item selected')">
			<template #icon>
				<School :size="65" />
			</template>
		</NcEmptyContent>
	</NcAppContentDetails>
</template>

<script>
import NcAppContentDetails from '@nextcloud/vue/dist/Components/NcAppContentDetails'
import NcRichContenteditable from '@nextcloud/vue/dist/Components/NcRichContenteditable'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent'
import NcRichText from '@nextcloud/vue/dist/Components/NcRichText'
import NcButton from '@nextcloud/vue/dist/Components/NcButton'

import Delete from 'vue-material-design-icons/Delete.vue';
import School from 'vue-material-design-icons/School.vue';
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue';

import AuthorEditList from './AuthorEditList.vue'

import { showError } from '@nextcloud/dialogs'
import { fetchScholarItemDetails } from './service/ScholarItemService'
import { createLibraryItemDetailed } from './service/LibraryItemService'

export default {
	name: 'ScholarItemDetails',
	components: {
		// components
		NcAppContentDetails,
		NcRichContenteditable,
		NcEmptyContent,
		NcRichText,
		NcButton,

		// icons
		Delete,
		School,
		OpenInNew,

		// project components
		AuthorEditList
	},
	data() {
		return {
			scholarItem: null,
			alertExcerpts: [],
			authorListDisplay: null,
			authorList: [],
		}
	},
	computed: {
		scholarItemId() {
			return parseInt(this.$route.params.inboxItemId, 0)
		},
	},
	watch: {
		scholarItemId: async function (scholarItemId) {
			if (!scholarItemId) return;
			try {
				let scholarItemDetails = await fetchScholarItemDetails(scholarItemId);
				this.scholarItem = scholarItemDetails.scholarItem;
				if (!this.scholarItem.journal) this.scholarItem.journal = "";
				if (!this.scholarItem.authors) this.scholarItem.authors = "";
				this.authorListDisplay = this.scholarItem.authors;
				this.alertExcerpts = scholarItemDetails.alertExcerpts;
				this.alertExcerpts.sort(function(a, b) {
					return parseFloat(a.importance) - parseFloat(b.importance);
				});
			} catch (e) {
				console.error(e)
				showError(t('athenaeum', 'Could not fetch scholar item details (route mounting failed)'))
			}
		},
		scholarItem: async function (scholarItem) {
			if (!scholarItem || !this.$options || !this.$options.authorListInterface) return;
			this.$options.authorListInterface.setAuthorListFromText(this.scholarItem.authors);
		},
	},
	methods: {
		// Setting the interface when emitted from child
		setAuthorListInterface(authorListInterface) {
			this.$options.authorListInterface = authorListInterface;
			this.$options.authorListInterface.setAuthorListFromText(this.scholarItem.authors)
		},
		authorListUpdated(newAuthorList) {
			this.authorList = newAuthorList;
			this.authorListDisplay = newAuthorList.map(author => author.displayName).join(', ');
		},
		addToLibrary() {
			let detailedItem = this.scholarItem;
			detailedItem.authorList = this.authorList;
			createLibraryItemDetailed(detailedItem);
		},
		markScholarItemDeleted() {
			this.scholarItem = null
		},
		hasEllipsis(text) {
			return text.includes('…') || text.includes("...")
		},
	},
}

</script>


<style lang="scss" scoped>

	.input-field {
		margin: 8px 0px;
	}

	.rich-contenteditable__input {
		text-align: initial;
		&[error="true"] {
			border-color: var(--color-error) !important;
		}
	}

	.rich-contenteditable__input--empty:before {
		position: inherit;
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