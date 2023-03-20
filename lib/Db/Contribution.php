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
 * @method getTitle(): string
 * @method setTitle(string $title): void
 * @method getContent(): string
 * @method setContent(string $content): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 */
class Contribution extends Entity implements JsonSerializable {
	protected int $itemId = 0;
	protected int $contributorId = 0;
	protected string $contributorNameDisplay = '';
	protected string $contributionTypeId = 0;
	protected int $contributionOrder = -1;

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'itemId' => $this->itemId,
			'contributorId' => $this->contributorId,
			'contributorNameDisplay' => $this->contributorNameDisplay,
			'contributionTypeId' => $this->contributionTypeId,
			'contributionOrder' => $this->contributionOrder
		];
	}
}
