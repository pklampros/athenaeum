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
 * @method getContributionId(): int
 * @method setContributionId(int $contributorId): void
 * @method getContributorNameDisplay(): string
 * @method setContributorNameDisplay(string $contributorNameDisplay): void
 * @method getContributionTypeId(): int
 * @method setContributionTypeId(int $contributionTypeId): void
 * @method getContributionOrder(): int
 * @method setContributionOrder(int $contributionOrder): void
 */
class Contribution extends Entity implements JsonSerializable {
	protected int $itemId = 0;
	protected int $contributorId = 0;
	protected string $contributorNameDisplay = '';
	protected int $contributionTypeId = 0;
	protected int $contributionOrder = -1;

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'itemId' => $this->itemId,
			'contributorId' => $this->contributorId,
			'contributorNameDisplay' => $this->contributorNameDisplay,
			'contributionTypeId' => $this->contributionTypeId,
			'contributionOrder' => $this->contributionOrder
		];
	}
}
