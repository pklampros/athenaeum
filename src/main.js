/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { generateFilePath, generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

import Vue from 'vue'
import App from './App.vue'
import router from './router.js'

// eslint-disable-next-line
__webpack_public_path__ = generateFilePath(appName, '', 'js/')

// eslint-disable-next-line
const providedAppName = appName;

Vue.mixin({ methods: { t, n } })

const userInitialised = await (async function() {
	const url = generateUrl('/apps/{appName}/api/0.1/app_info/user_init',
		{ appName: providedAppName })
	let udbid = false
	await axios.get(url)
		.then(function(response) {
			udbid = response.data
		})
	return udbid
})()

if (!userInitialised) {
	throw new Error('User not initialised')
}
export default new Vue({
	el: '#content',
	router,
	render: h => h(App),
})
