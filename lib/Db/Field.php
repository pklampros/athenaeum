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
class Field extends Entity implements JsonSerializable {
	protected string $name = '';
	protected string $typeHint = ''; // "string", "int", "float", "bool"

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'typeHint' => $this->typeHint
		];
	}
}
