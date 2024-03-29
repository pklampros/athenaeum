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
 */
class ContributionType extends Entity implements JsonSerializable {
	protected int $name = 0;

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name
		];
	}
}
