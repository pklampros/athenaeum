<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcModal :show="visible"
		@close="closeModal">
		<div ref="modalContent"
			class="modal__content">
			<h2 style="padding: 0px 10px;">
				Upload Attachmens
			</h2>
			<div class="file-list">
				<div v-if="files.length === 0"
					style="text-align: center">
					<h3>No files selected</h3>
					<h3>Press Browse to select some from the filesystem</h3>
				</div>
				<ul v-else>
					<div v-for="(file, index) in files"
						:key="file">
						<NcListItem :name="file.name"
							@click="toggleItemsVisible(index)">
							<template #indicator>
								<CheckboxBlankCircle v-if="file.state == 'local'"
									v-model="file.state"
									:size="20" />
								<NcLoadingIcon v-else-if="file.state == 'saving'"
									v-model="file.state"
									:size="20" />
								<CheckCircle v-else-if="file.state == 'saved'"
									v-model="file.state"
									:size="20"
									fill-color="green" />
								<CheckCircle v-else-if="file.state == 'exists'"
									v-model="file.state"
									:size="20"
									fill-color="yellow" />
							</template>
						</NcListItem>
					</div>
				</ul>
			</div>
			<div style="display:flex; justify-content: right; align-items: center;">
				<!-- This button clicks the input below it. Not an ideal solution but
				adding a label inside the button (to use with "for") did not work -->
				<NcButton arialabel="Browse for files to import"
					type="primary"
					@click="$refs.attachmentUploadInput.click();">
					Browse...
				</NcButton>
				<input id="attch-upload-input"
					ref="attachmentUploadInput"
					type="file"
					style="display:none"
					multiple
					@change="filesSelected($event)">

				<div style="flex-grow: 1;" />
				<NcButton :disabled="files.length === 0"
					type="primary"
					@click="submitFiles">
					Upload
				</NcButton>
				&nbsp;
				<NcButton type="primary"
					@click="closeModal">
					Close
				</NcButton>
			</div>
		</div>
	</NcModal>
</template>

<script>

import { NcModal, NcButton, NcListItem, NcLoadingIcon } from '@nextcloud/vue'

import CheckboxBlankCircle from 'vue-material-design-icons/CheckboxBlankCircle.vue'
import CheckCircle from 'vue-material-design-icons/CheckCircle.vue'

import { attachFiles } from './service/ItemService.js'

export default {
	name: 'AttachmentUploadModal',
	components: {
		// components
		NcModal,
		NcButton,
		NcListItem,

		// icons
		CheckboxBlankCircle,
		NcLoadingIcon,
		CheckCircle,
	},
	props: {
		visible: {
			type: Boolean,
			required: true,
		},
		itemId: {
			type: Number,
			required: true,
		},
	},
	data() {
		return {
			files: [],
		}
	},
	computed: {
	},
	methods: {
		toggleItemsVisible(fi) {
			const fo = this.files[fi]
			fo.itemsVisible = !fo.itemsVisible
			this.$set(this.files, fi, fo)
		},
		filesSelected(event) {
			this.files = []
			const selectedFiles = []
			for (let i = 0; i < event.target.files.length; i++) {
				const fo = event.target.files[i]
				fo.state = 'local'
				fo.items = []
				fo.itemsVisible = false
				selectedFiles.push(fo)
			}
			this.files = selectedFiles

			// this ugly hack is required because it is not possible
			// to address the parent node of the modal through CSS
			// directly as it's a different component
			this.$refs.modalContent.parentNode.style.display = 'flex'
		},
		async submitFiles() {
			this.uploading = true
			for (const fidx in this.files) {
				const file = this.files[fidx]
				file.state = 'saving'
				this.$set(this.files, fidx, file)
			}
			const result = await attachFiles(this.files, this.itemId)
			for (const fidx in result) {
				const file = this.files[fidx]
				file.state = result[fidx].state
				this.$set(this.files, fidx, file)
			}
			this.uploading = false
		},
		closeModal() {
			// eslint-disable-next-line vue/custom-event-name-casing
			this.$emit('modalClosed')
		},
	},
}
</script>

<style lang="scss" scoped>
:deep(.modal__content) {
	margin: 10px;
	display: flex;
	flex-direction: column;
	flex: 1;
}

:deep(.list-item) {
	box-sizing: border-box
}

:deep(.file-list) {
	flex-grow: 1;
	overflow: auto;
	padding: 10px;
	margin: 10px 0px;
	border-radius: 16px;
	border: 2px solid var(--color-border);
}
</style>
