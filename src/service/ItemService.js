/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

import { convertAxiosError } from '../errors/convert.js'

/**
 *
 * @param {string} folder Filter items by this folder
 * @param {object} offset Number of items to skip
 * @param {number} limit Limit to a particular number of items
 * @param {string} query Search query
 */
export function fetchItems(folder, offset, limit, query) {
	const url = generateUrl('/apps/athenaeum/res/items')
	const params = {
	}

	if (folder) {
		params.folder = folder
	}
	if (limit) {
		params.limit = limit
	}
	if (offset) {
		params.offset = offset
	}
	if (query) {
		params.filter = query
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
export function fetchItemSummary(id) {
	const url = generateUrl('/apps/athenaeum/item/summary/' + id)
	const params = {
	}

	return axios
		.get(url, {
			params,
		})
		.catch((error) => {
			throw convertAxiosError(error)
		})
}

/**
 *
 * @param {number} id Item id
 */
export function fetchItemDetails(id) {
	const url = generateUrl('/apps/athenaeum/res/items/' + id)
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
 * @param {string} newFolder The folder to send the item to
 */
export function itemChangeFolder(id, newFolder) {
	const url = generateUrl('/apps/athenaeum/mod/items/folder')

	return axios
		.post(url, {
			id,
			folder: newFolder,
		}, {
			headers: {
				'Content-Type': 'application/json',
			},
		})
		.then((resp) => resp.data)
		.catch((error) => {
			throw convertAxiosError(error)
		})
}

/**
 *
 * @param {object} itemData Detailed item information to save
 */
export function convertToLibraryItemDetailed(itemData) {
	const url = generateUrl('/apps/athenaeum/inbox_items/toLibrary')
	return axios
		.post(url, {
			itemData,
		}, {
			headers: {
				'Content-Type': 'application/json',
			},
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
export function dumpToJSON(id) {
	const url = generateUrl('/apps/athenaeum/items/dump/' + id)
	const params = {}

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
 * @param {string} file The filt to attach
 * @param {number} itemId The item to attach the file to
 */
export function attachFile(file, itemId) {
	const url = generateUrl('/apps/athenaeum/items/attachFile')

	const formData = new FormData()
	formData.append('file', file)
	formData.append('item_id', itemId)
	return axios
		.post(url, formData,
			{
				headers: {
					'Content-Type': 'multipart/form-data',
				},
			})
		.then((resp) => resp.data)
		.catch((error) => {
			throw convertAxiosError(error)
		})
}
