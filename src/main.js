/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { sync } from 'vuex-router-sync'
import { generateFilePath } from '@nextcloud/router'

import Vue from 'vue'
import App from './App.vue'
import router from './router.js'
import store from './store/index.js'

// eslint-disable-next-line
__webpack_public_path__ = generateFilePath(appName, '', 'js/')

sync(store, router)

Vue.mixin({ methods: { t, n } })

export default new Vue({
	el: '#content',
	router,
	store,
	render: h => h(App),
})
