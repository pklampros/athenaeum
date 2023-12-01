/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

import { convertAxiosError } from '../errors/convert.js'

/**
 *
 */
export function fetchSources() {
	const url = generateUrl('/apps/athenaeum/res/sources')
	const params = {
	}

	return axios
		.get(url, {
			params,
		})
		.then((resp) => resp.data)
		.catch((error) => {
			throw convertAxiosError(error)
		})
}

/**
 *
 * @param {number} id Item id
 */
export function fetchSourceDetails(id) {
	const url = generateUrl('/apps/athenaeum/res/sources/' + id)
	const params = {
	}

	return axios
		.get(url, {
			params,
		})
		.then((resp) => resp.data)
		.catch((error) => {
			throw convertAxiosError(error)
		})
}

/**
 *
 * @param {number} id Source id
 * @param {number} importance Source importance (for sorting related items)
 * @param {string} title Source title
 * @param {string} description Source description
 */
export function updateSource(id, importance, title,
							 description) {
	const url = generateUrl('/apps/athenaeum/res/sources/' + id)

	return axios
		.put(url, {
			importance,
			title,
			description,
		})
		.then((resp) => resp.data)
		.catch((error) => {
			throw convertAxiosError(error)
		})
}
