/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

import { convertAxiosError } from '../errors/convert'

export function createItemDetailed(itemData) {
	const url = generateUrl('/apps/athenaeum/items/new/detailed')

	console.log(itemData);
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