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
 * @param {string} query Search query
 * @param {object} cursor Current item
 * @param {number} limit Limit to a particular number of items
 */
export function fetchItems(folder, query, cursor, limit) {
	const url = generateUrl('/apps/athenaeum/res/items')
	const params = {
	}

	if (folder) {
		params.folder = folder
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
 * @param newFolder
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
 * @param itemData
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
