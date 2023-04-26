<template>
	<div>
        <NcModal
        v-if="scholarItemBeingFiled"
        @close="cancelShelvingItem">
            <div class="modal__content">
                <h2>Shelve item details</h2>
                <NcTextField
				    label="Title"
				    :label-visible="true"
					:value.sync="scholarItemBeingFiled.title" />
                <NcTextField
				    label="URL"
				    :label-visible="true"
					:value.sync="scholarItemBeingFiled.url" />
                <NcTextField
				    label="Journal"
				    :label-visible="true"
					:value.sync="scholarItemBeingFiled.journal"
					v-if="scholarItemBeingFiled.journal" />
                <!-- <NcTextField label="Authors" :value.sync="scholarItemBeingFiled.authors" /> -->
				<NcTextField v-for="author in scholarItemBeingFiled.authorList"
					:key="author.name"
					:label="author.displayName"
					:value.sync="author.name">
				</NcTextField>
                <NcButton
                    :disabled="!this.scholarItemBeingFiled.title || !this.scholarItemBeingFiled.url"
                    @click="cancelShelvingItem"
                    type="primary">
                    Submit
                </NcButton>
                <NcButton
                    :disabled="!this.scholarItemBeingFiled.title || !this.scholarItemBeingFiled.url"
                    @click="markScholarItemDeleted"
                    type="primary">
                    Move to trash
                </NcButton>
            </div>
        </NcModal>
	</div>
</template>
<script>

import NcTextField from '@nextcloud/vue/dist/Components/NcTextField'
import NcButton from '@nextcloud/vue/dist/Components/NcButton'
import NcModal from '@nextcloud/vue/dist/Components/NcModal'

export default {
	name: 'ItemFileModal',
	components: {
		NcTextField,
		NcButton,
		NcModal,
    },
	data() {
		return {
			scholarItemBeingFiled: null,
		}
	},
	methods: {
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
			let authorList = this.scholarItemBeingFiled.authors.split(",");
			let hasMoreAuthors = false;
			authorList = authorList.map((author) => {
				if (author.endsWith("…")) {
					author = author.slice(0, -1);
					hasMoreAuthors = true;
				}
				author = author.trim()
				return {"name": author, "displayName": author}
			});
			if (hasMoreAuthors) {
				authorList.push({"name": "…", "displayName": "…"})
			}
			this.scholarItemBeingFiled.authorList = authorList;
		},
		cancelShelvingItem() {
			this.scholarItemBeingFiled = null
		},
		markScholarItemDeleted() {
			this.scholarItemBeingFiled = null
		},
	},
}
</script>

<style scoped>
    .modal__content {
        margin: 50px;
        text-align: center;
    }

    .input-field {
        margin: 12px 0px;
    }
</style>