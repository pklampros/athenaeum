<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

/**
 * @method getId(): int
 * @method getUserId(string $userId): string
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
class ScholarItem extends Entity implements JsonSerializable {
	protected string $url = '';
	protected string $title = '';
	protected string $authors = '';
	protected string $journal = '';
	protected $published;
	protected bool $read = 0;
	protected int $importance = 0;
	protected bool $needsReview = false;
	protected string $userId = '';
	
	public function __construct() {
		$this->addType('published', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'url' => $this->url,
			'title' => $this->title,
			'authors' => $this->authors,
			'journal' => $this->journal,
			'published' => $this->published,
			'read' => $this->read,
			'importance' => $this->importance,
			'needsReview' => $this->needsReview,
			'userId' => $this->userId
		];
	}
}
