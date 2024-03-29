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
 * @method getTagId(): int
 * @method setTagId(int $tagId): void
 */
class ItemTag extends Entity implements JsonSerializable {
	protected int $itemId = 0;
	protected int $tagId = 0;

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'itemId' => $this->itemId,
			'tagId' => $this->tagId
		];
	}
}
