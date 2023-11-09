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
 * @method getUid(): string
 * @method setUid(string $uid): void
 * @method getSourceType(): string
 * @method setSourceType(string $sourceType): void
 * @method getImportance(): string
 * @method setImportance(int $importance): void
 * @method getTitle(): string
 * @method setTitle(string $title): void
 * @method getDescription(): string
 * @method setDescription(string $description): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 */
class Source extends Entity implements JsonSerializable {
	protected string $uid = '';
	protected string $sourceType = '';
	protected int $importance = 0;
	protected string $title = '';
	protected string $description = '';
	protected string $userId = '';

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'uid' => $this->uid,
			'sourceType' => $this->sourceType,
			'importance' => $this->importance,
			'title' => $this->title,
			'description' => $this->description,
			'userId' => $this->userId
		];
	}
}
