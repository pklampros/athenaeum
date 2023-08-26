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
 * @method getName(): string
 * @method setName(string $name): void
 * @method getColuor(): string
 * @method setColour(string $colour): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 */
class Tag extends Entity implements JsonSerializable {
	protected string $name = '';
	protected string $colour = '';
	protected string $userId = '';

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'colour' => $this->colour,
			'userId' => $this->userId
		];
	}
}
