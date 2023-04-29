<template>
	<div>
		<NcModal
		v-if="scholarItemBeingFiled"
		@close="cancelShelvingItem">
			<div class="modal__content">
				<h2>Shelve item details</h2>
				<div class="field-label">
					<h3>Title</h3>
				</div>
				<NcRichContenteditable
					placeholder="Title"
					:error="hasEllipsis(scholarItemBeingFiled.title)"
					:value.sync="scholarItemBeingFiled.title" />
				<div class="field-label">
					<h3>URL</h3>
				</div>
				<NcRichContenteditable
					placeholder="URL"
					:value.sync="scholarItemBeingFiled.url" />
				<div class="field-label">
					<h3>Journal</h3>
				</div>
				<NcRichContenteditable
					placeholder="Journal"
					:error="hasEllipsis(scholarItemBeingFiled.journal)"
					:value.sync="scholarItemBeingFiled.journal"
					v-if="scholarItemBeingFiled.journal" />
				<AuthorEditList
					@interface="setAuthorListInterface"/>
				<div style="display: flex; justify-content: right; align-items: center; ">
					<NcButton
						ariaLabel="Remove scholar item"
						:disabled="!this.scholarItemBeingFiled.title || !this.scholarItemBeingFiled.url"
						@click="markScholarItemDeleted"
						type="primary">
						<template #icon>
							<Delete :size="20" />
						</template>
					</NcButton>
					&nbsp;
					<NcButton
						:disabled="!this.scholarItemBeingFiled.title || !this.scholarItemBeingFiled.url"
						@click="cancelShelvingItem"
						type="primary">
						Submit
					</NcButton>
				</div>
			</div>
		</NcModal>
	</div>
</template>
<script>

import NcModal from '@nextcloud/vue/dist/Components/NcModal'
import NcRichContenteditable from '@nextcloud/vue/dist/Components/NcRichContenteditable'
import NcButton from '@nextcloud/vue/dist/Components/NcButton'

import Delete from 'vue-material-design-icons/Delete.vue';

import AuthorEditList from './AuthorEditList.vue'

export default {
	name: 'ItemFileModal',
	components: {
		// components
		NcModal,
		NcRichContenteditable,
		NcButton,

		// icons
		Delete,

		// project components
		AuthorEditList
	},
	data() {
		return {
			scholarItemBeingFiled: null
		}
	},
	authorListInterface: {
      setAuthorListFromText: () => {}
    },
	methods: {
		// Setting the interface when emitted from child
		setAuthorListInterface(authorListInterface) {
        console.log("setAuthorListInterface!!!")
			this.$options.authorListInterface = authorListInterface;
			this.$options.authorListInterface.setAuthorListFromText(this.scholarItemBeingFiled.authors)
		},
		shelveItem(scholarItem) {
			// pop up dialog to decide what to do with this item
			// it should show title, authors, excerpt(s) and only 
			// allow filing if the author's list is complete. Perhaps
			// do the author-splitting here?

			// this dialog should also allow for marking items as "read"
			// with importance = 0, but not in the inbox anymore. Perhaps
			// these items should go into an "Read items" folder, sorted
			// by date of opening/marking as read, so as to allow for
			// keeping them in case a mistake was made. Then perhaps
			// delete them after some time? Or compact them?

			// clickin on an item (n)
			this.scholarItemBeingFiled = scholarItem
			// this.$options.authorListInterface.setAuthorListFromText(this.scholarItemBeingFiled.authors)
		},
		cancelShelvingItem() {
			this.scholarItemBeingFiled = null
		},
		markScholarItemDeleted() {
			this.scholarItemBeingFiled = null
		},
		hasEllipsis(text) {
			return text.includes('…') || text.includes("...")
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

	.rich-contenteditable__input {
		text-align: initial;
		&[error="true"] {
			border-color: var(--color-error) !important;
		}
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