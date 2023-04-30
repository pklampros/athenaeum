<template>
	<NcAppContentDetails>
		{{ scholarItemId }}
	</NcAppContentDetails>
</template>

<script>
import NcAppContentDetails from '@nextcloud/vue/dist/Components/NcAppContentDetails'

export default {
	name: 'ScholarItemDetails',
	components: {
		NcAppContentDetails,
	},
	computed: {
		scholarItemId() {
			return parseInt(this.$route.params.scholarItemId, 0)
		},
	}
}

</script>