<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

/**
 * @method getId(): int
 * @method getTitle(): string
 * @method setTitle(string $title): void
 * @method getItemTypeId(): int
 * @method setItemTypeId(int $itemTypeId): void
 * @method getDateAdded(): \DateTime
 * @method setDateAdded(DateTime $dateAdded): void
 * @method getDateModified(): \DateTime
 * @method setDateModified(DateTime $dateModified): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 */
class Item extends Entity implements JsonSerializable {
	protected string $title = '';
	protected int $itemTypeId = 0;
	//protected ?\DateTime $dateAdded = NULL; # not possible to set a default DateTime as it's an object
	//protected ?\DateTime $dateModified = NULL; # not possible to set a default DateTime as it's an object
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
			'title' => $this->title,
			'itemTypeId' => $this->itemTypeId,
			'dateAdded' => $this->dateAdded,
			'dateModified' => $this->dateModified,
			'userId' => $this->userId
		];
	}
}
