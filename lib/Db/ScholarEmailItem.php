<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

/**
 * @method getId(): int
 * @method getTitle(): string
 * @method setTitle(string $title): void
 * @method getContent(): string
 * @method setContent(string $content): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 */
class ScholarEmailItem extends Entity implements JsonSerializable {
	protected int $emailId = 0;
	protected int $itemId = 0;
	protected string $excerpt = '';

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'emailId' => $this->emailId,
			'itemId' => $this->itemId,
			'excerpt' => $this->excerpt
		];
	}
}
