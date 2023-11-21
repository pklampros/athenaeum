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
 * @method getPath(): string
 * @method setPath(string $path): void
 * @method getEditable(): bool
 * @method setEditable(bool $editable): void
 * @method getIcon(): string
 * @method setIcon(string $icon): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 */
class Folder extends Entity implements JsonSerializable {
	protected string $path = '';
	protected string $name = '';
	protected bool $editable = false;
	protected string $icon = '';
	protected string $userId = '';

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'path' => $this->path,
			'name' => $this->name,
			'editable' => $this->editable,
			'icon' => $this->icon,
			'userId' => $this->userId
		];
	}
}
