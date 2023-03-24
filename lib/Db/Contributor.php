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
 * @method getFirstName(): string
 * @method setFirstName(string $firstName): void
 * @method getFirstNameIsFullName(): bool
 * @method setFirstNameIsFullName(bool $firstNameIsFullName): void
 * @method getLastName(): string
 * @method setLastName(string $lastName): void
 */
class Contributor extends Entity implements JsonSerializable {
	protected string $firstName = '';
	protected bool $firstNameIsFullName = false;
	protected ?string $lastName = '';

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'firstName' => $this->firstName,
			'firstNameIsFullName' => $this->firstNameIsFullName,
			'lastName' => $this->lastName
		];
	}
}
