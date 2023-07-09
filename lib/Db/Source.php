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
 * @method getImportanceDecided(): bool
 * @method setImportanceDecided(bool $importanceDecided): void
 */
class Source extends Entity implements JsonSerializable {
	protected string $uid = '';
	protected string $sourceType = '';
	protected int $importance = 0;

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'uid' => $this->uid,
			'sourceType' => $this->sourceType,
			'importance' => $this->importance
		];
	}
}
