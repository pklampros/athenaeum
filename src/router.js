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
			path: '/items/:folder',
			name: 'items',
			component: App,
		},
		{
			path: '/items/:folder/:itemId',
			name: 'items_details',
			component: App,
		},
		{
			path: '/sources',
			name: 'sources',
			component: App,
		},
		{
			path: '/sources/:sourceId',
			name: 'sources_details',
			component: App,
		},
	],
})