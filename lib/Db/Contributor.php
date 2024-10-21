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
 * @method getLastNameIsFullName(): bool
 * @method setLastNameIsFullName(bool $lastNameIsFullName): void
 * @method getLastName(): string
 * @method setLastName(string $lastName): void
 * @method getDateAdded(): \DateTime
 * @method setDateAdded(DateTime $dateAdded): void
 * @method getDateModified(): \DateTime
 * @method setDateModified(DateTime $dateModified): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 */
class Contributor extends Entity implements JsonSerializable {
	protected string $firstName = '';
	protected bool $lastNameIsFullName = false;
	protected ?string $lastName = '';
	protected $dateAdded;
	protected $dateModified;
	protected string $userId = '';

	public function __construct() {
		$this->addType('dateAdded', 'datetime');
		$this->addType('dateModified', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'firstName' => $this->firstName,
			'lastNameIsFullName' => $this->lastNameIsFullName,
			'lastName' => $this->lastName,
			'dateAdded' => $this->dateAdded->getTimeStamp(),
			'dateModified' => $this->dateModified->getTimeStamp(),
			'userId' => $this->userId
		];
	}
}
