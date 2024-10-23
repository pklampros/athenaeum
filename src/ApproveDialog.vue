<template>
	<!--
	SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
	SPDX-License-Identifier: AGPL-3.0-or-later
	-->
	<NcDialog :open="visible"
		:name="name"
		:message="message"
		:buttons="buttons" />
</template>

<script>
import {
	NcDialog,
} from '@nextcloud/vue'

export default {
	name: 'ApproveDialog',
	components: {
		// components
		NcDialog,
	},
	props: {
		visible: {
			type: Boolean,
			required: true,
		},
		name: {
			type: String,
			required: true,
		},
		message: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			buttons: [
				{
					label: 'Cancel',
					callback: () => { this.$emit('dialog-cancel') },
				},
				{
					label: 'Ok',
					type: 'primary',
					callback: () => { this.$emit('dialog-ok') },
				},
			],
		}
	},
}
</script>
