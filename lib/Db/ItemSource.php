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
 * @method getSourceId(): int
 * @method setSourceId(int $sourceId): void
 * @method getExtra(): string
 * @method setExtra(string $extra): void
 */
class ItemSource extends Entity implements JsonSerializable {
	protected int $itemId = 0;
	protected int $sourceId = 0;
	protected ?string $extra = '';

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'itemId' => $this->itemId,
			'sourceId' => $this->sourceId,
			'extra' => $this->extra
		];
	}
}
