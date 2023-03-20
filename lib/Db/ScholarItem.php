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
 * @method getUrl(): string
 * @method setUrl(string $url): void
 * @method getTitle(): string
 * @method setTitle(string $title): void
 * @method getAuthors(): string
 * @method setAuthors(string $authors): void
 * @method getJournal(): string
 * @method setJournal(string $journal): void
 * @method getPublished(): string
 * @method setPublished(string $published): void
 * @method getRead(): bool
 * @method setRead(bool $read): void
 * @method getImportance(): int
 * @method setImportance(int $importance): void
 * @method getNeedsReview(): bool
 * @method setNeedsReview(bool $needsReview): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 */
class ScholarItem extends Entity implements JsonSerializable {
	protected string $url = '';
	protected string $title = '';
	protected string $authors = '';
	protected string $journal = '';
	protected string $published = '';
	protected bool $read = false;
	protected int $importance = 0;
	protected bool $needsReview = false;
	protected string $userId = '';

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
