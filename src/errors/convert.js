/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import InboxItemNotFoundError from './InboxItemNotFoundError.js'

const map = {
	'Exception\\InboxItemNotFoundError': InboxItemNotFoundError,
}

/**
 * @param {object} axiosError the axios Error
 * @return {Error}
 */
export const convertAxiosError = (axiosError) => {
	if (!('response' in axiosError)) {
		// No conversion
		return axiosError
	}

	if (!('x-mail-response' in axiosError.response.headers)) {
		// Not a structured response
		return axiosError
	}

	const response = axiosError.response
	if (!(response.data.data.type in map)) {
		// No conversion possible
		return axiosError
	}

	return new map[response.data.data.type](response.data.message)
}
