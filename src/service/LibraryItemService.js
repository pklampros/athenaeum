/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

import { convertAxiosError } from '../errors/convert'

export function fetchLibraryItems(query, cursor, limit) {
	const url = generateUrl('/apps/athenaeum/items')
	const params = {
	}

	if (query) {
		params.filter = query
	}
	if (limit) {
		params.limit = limit
	}
	if (cursor) {
		params.cursor = cursor
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

export function fetchLibraryItemDetails(id) {
	const url = generateUrl('/apps/athenaeum/items/details/' + id)
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

export function createItemDetailed(itemData) {
	const url = generateUrl('/apps/athenaeum/items/new/detailed')

	return axios
		.post(url, {
			itemData,
		}, {
			headers: {
				'Content-Type': 'application/json'
			}
		})
		.then((resp) => resp.data)
		.catch((error) => {
			throw convertAxiosError(error)
		})
}