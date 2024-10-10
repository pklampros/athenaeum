/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

/**
 * Returns the maximum number of file uploads allowed
 * by the server
 */
export async function getMaxFileUploads() {
	let maxFileUploads = 1
	await axios.get(
		generateUrl('/apps/athenaeum/api/0.1/app_info/max_file_uploads'),
	).then(function(response) {
		maxFileUploads = ((response.data === '0') ? 1 : response.data)
	})
	return parseInt(maxFileUploads)
}