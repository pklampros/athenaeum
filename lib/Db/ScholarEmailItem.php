<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

/**
 * @method getId(): int
 * 
 * @method getScholarEmailId(): int
 * @method setScholarEmailId(int $scholarEmailId): void
 * @method getScholarItemId(): int
 * @method setScholarItemId(int $scholarItemId): void
 * @method getExcerpt(): string
 * @method setExcerpt(string $excerpt): void
 */
class ScholarEmailItem extends Entity implements JsonSerializable {
	protected int $scholarEmailId = 0;
	protected int $scholarItemId = 0;
	protected ?string $excerpt = '';

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'scholarEmailId' => $this->scholarEmailId,
			'scholarItemId' => $this->scholarItemId,
			'excerpt' => $this->excerpt
		];
	}
}
