/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

export default class ScholarItemNotFoundError extends Error {

	constructor(message) {
		super(message)
		this.name = ScholarItemNotFoundError.getName()
	}

	static getName() {
		return 'ScholarItemNotFoundError'
	}

}