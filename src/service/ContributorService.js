// SPDX-FileCopyrightText: 2023 Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

import { convertAxiosError } from '../errors/convert'

export function findSimilar(firstName, lastName, displayName) {
	const url = generateUrl('/apps/athenaeum/contributors/similar');
	const names = {
		"firstName": firstName,
		"lastName": lastName,
		"displayName": displayName,
	}

	return axios
		.post(url, {
			names,
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