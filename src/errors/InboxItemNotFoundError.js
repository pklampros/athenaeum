/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

export default class InboxItemNotFoundError extends Error {

	constructor(message) {
		super(message)
		this.name = InboxItemNotFoundError.getName()
	}

	static getName() {
		return 'InboxItemNotFoundError'
	}

}