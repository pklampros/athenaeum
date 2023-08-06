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
 * @method getItemId(): int
 * @method setItemId(int $itemId): void
 * @method getPath(): string
 * @method setPath(string $path): void
 * @method getMimeType(): string
 * @method setMimeType(string $mimeType): void
 * @method getNotes(): string
 * @method setNotes(string $notes): void
 */
class ItemAttachment extends Entity implements JsonSerializable {
	protected int $itemId = 0;
	protected string $path = "";
	protected ?string $mimeType = "";
	protected ?string $notes = "";

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'itemId' => $this->itemId,
			'path' => $this->path,
			'mimeType' => $this->mimeType,
			'notes' => $this->notes
		];
	}
}
