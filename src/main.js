/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { generateFilePath, generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { t, n } from '@nextcloud/l10n'

// eslint-disable-next-line
__webpack_public_path__ = generateFilePath(appName, '', 'js/')

// eslint-disable-next-line
const providedAppName = appName;

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

const app = createApp(App)
app.use(router)

app.config.globalProperties.t = t
app.config.globalProperties.n = n

app.mount('#content')
