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
 * @method getScholarId(): string
 * @method setScholarId(string $scholarId): void
 * @method getTerm(): string
 * @method setTerm(string $term): void
 * @method getImportance(): string
 * @method setImportance(int $importance): void
 * @method getImportanceDecided(): bool
 * @method setImportanceDecided(bool $importanceDecided): void
 */
class ScholarAlert extends Entity implements JsonSerializable {
	protected string $scholarId = '';
	protected string $term = '';
	protected int $importance = 0;
	protected bool $importanceDecided = false;

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'scholarId' => $this->scholarId,
			'term' => $this->term,
			'importance' => $this->importance,
			'importanceDecided' => $this->importanceDecided
		];
	}
}
