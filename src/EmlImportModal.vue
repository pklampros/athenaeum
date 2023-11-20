<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcModal @close="closeModal">
		<div ref="modalContent" class="modal__content">
			<h2 style="padding: 0px 10px;">
				Scholar EML Importer
			</h2>
			<div class="file-list">
				<div v-if="files.length === 0" style="text-align: center">
					<h3>No files selected</h3>
					<h3>Press Browse to select some from the filesystem</h3>
				</div>
				<ul v-else>
					<div v-for="(file, index) in files" :key="file">
						<NcListItem :title="file.name"
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
						<ul v-show="file.itemsVisible && file.items.length != 0" style="padding-left: 2em">
							<NcListItem v-for="item in file.items"
								:key="item"
								:title="item.title"
								compact="true"
								:href="goToItem(item)"
								target="_blank">
								<template #indicator>
									<div style="display:flex;">
										<CheckCircle v-if="item.item_new"
											v-model="item.item_new"
											:size="18"
											title="New item created"
											aria-label="New item created"
											fill-color="green" />
										<CheckCircle v-else
											v-model="item.item_new"
											:size="18"
											title="Item already exists"
											aria-label="Item already exists"
											fill-color="yellow" />

										<CheckCircle v-if="item.item_source_new"
											v-model="item.item_source_new"
											:size="18"
											title="Item in new source"
											arialabel="Item in new source"
											fill-color="green" />
										<CheckCircle v-else
											v-model="item.item_source_new"
											:size="18"
											title="Item in existing source"
											arialabel="Item in existing source"
											fill-color="yellow" />
										&nbsp;
										<OpenInNew v-model="item.item_source_new"
											:size="18"
											title="Open item"
											arialabel="Click to open item" />
									</div>
								</template>
							</NcListItem>
						</ul>
					</div>
				</ul>
			</div>
			<div style="display:flex; justify-content: right; align-items: center;">
				<!-- This button clicks the input below it. Not an ideal solution but
				adding a label inside the button (to use with "for") did not work -->
				<NcButton arialabel="Browse for EML files to import"
					type="primary"
					@click="$refs.emlUploadInput.click();">
					Browse...
				</NcButton>
				<input id="eml-upload-input"
					ref="emlUploadInput"
					type="file"
					style="display:none"
					multiple
					@change="filesSelected($event)">

				<div style="flex-grow: 1;" />
				<NcButton :disabled="files.length === 0"
					type="primary"
					@click="submitFiles">
					Import
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

import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcListItem from '@nextcloud/vue/dist/Components/NcListItem.js'

import CheckboxBlankCircle from 'vue-material-design-icons/CheckboxBlankCircle.vue'
import NcLoadingIcon from '@nextcloud/vue/dist/Components/NcLoadingIcon.js'
import CheckCircle from 'vue-material-design-icons/CheckCircle.vue'
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
	name: 'EmlImportModal',
	components: {
		// components
		NcModal,
		NcButton,
		NcListItem,

		// icons
		CheckboxBlankCircle,
		NcLoadingIcon,
		CheckCircle,
		OpenInNew,
	},
	props: {
		visible: {
			type: Boolean,
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
		goToItem(item) {
			return generateUrl('/apps/athenaeum/items/' + item.folderPath + '/' + item.id)
		},
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
		async getMaxFileUploads() {
			let maxFileUploads = 1
			await axios.get(
				generateUrl('/apps/athenaeum/api/0.1/app_info/max_file_uploads'),
			).then(function(response) {
				maxFileUploads = ((response.data === '0') ? 1 : response.data)
			})
			return parseInt(maxFileUploads)
		},
		async submitFiles() {
			const maxFileUploads = await this.getMaxFileUploads()
			const fileKeys = [...this.files.keys()]
			for (let i = 0; i < fileKeys.length; i += maxFileUploads) {
				const chunk = fileKeys.slice(i, i + maxFileUploads)
				await this.submitFileIndices(chunk)
			}
		},
		async submitFileIndices(indices) {
			this.uploading = true
			const formData = new FormData()
			const fileMetadata = {}
			let formDataIdx = 0
			for (const i of indices) {
				const fo = this.files[i]
				fo.state = 'saving'
				fo.id = i
				this.$set(this.files, i, fo)
				const newFileName = '' + i + '.eml'
				formData.append(formDataIdx, fo, newFileName)
				formDataIdx++
				fileMetadata[newFileName] = {
					name: fo.name,
				}
				fo.sentFilename = newFileName
			}
			formData.set('fileMetadata', JSON.stringify(fileMetadata))
			formData.set('fileCount', indices.length)
			const thisClass = this
			await axios.post(
				generateUrl('/apps/athenaeum/inbox_items/extractFromEML'),
				formData, {
					headers: {
						'Content-Type': 'multipart/form-data',
					},
				}).then(function(response) {
				for (const i of indices) {
					const fo = thisClass.files[i]
					fo.items = response.data[fo.sentFilename]
					fo.state = 'exists'
					for (const item of fo.items) {
						if (item.item_new || item.item_source_new) {
							fo.state = 'saved'
							break
						}
					}
					thisClass.$set(thisClass.files, i, fo)
				}
			}).catch(function(e) {
				for (const i of indices) {
					const fo = thisClass.files[i]
					fo.state = 'error'
					thisClass.$set(thisClass.files, i, fo)
				}
			}).finally(function() {
				thisClass.uploading = false
			})
		},
		closeModal() {
			this.$emit('modal-closed')
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
		margin:10px 0px;
		border-radius: 16px;
		border: 2px solid var(--color-border);
	}
</style>
