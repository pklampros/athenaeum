// SPDX-FileCopyrightText: 2023 Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

import { convertAxiosError } from '../errors/convert.js'

/**
 *
 * @param {string} firstName Author's first name
 * @param {string} lastName Author's last name
 * @param {string} displayName Author's complete display name
 */
export function findSimilar(firstName, lastName, displayName) {
	const url = generateUrl('/apps/athenaeum/contributors/similar')
	const names = {
		firstName,
		lastName,
		displayName,
	}

	return axios
		.post(url, {
			names,
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
 * @param {string} term The term to search for
 */
export function freeSearch(term) {
	const url = generateUrl('/apps/athenaeum/contributors/search')

	return axios
		.post(url, {
			term,
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
