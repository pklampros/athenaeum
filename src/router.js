/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import Router from 'vue-router'
import { generateUrl } from '@nextcloud/router'

const App = () => import('./App')

Vue.use(Router)

export default new Router({
	mode: 'history',
	base: generateUrl('/apps/athenaeum/'),
	linkActiveClass: 'active',
	routes: [
		{
			path: '/',
			name: 'home',
			component: App,
		},
		{
			path: '/inbox/',
			name: 'inbox',
			component: App,
		},
		{
			path: '/inbox/:inboxItemId',
			name: 'inbox_item',
			component: App,
		},
		{
			path: '/library/',
			name: 'library',
			component: App,
		},
		{
			path: '/library/:libraryItemId',
			name: 'library_item',
			component: App,
		},
	],
})