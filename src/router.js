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
			path: '/inbox/:scholarItemId',
			name: 'inbox_item',
			component: App,
		},
	],
})