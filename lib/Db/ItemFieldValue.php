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
 * @method getFieldId(): int
 * @method setFieldId(int $itemId): void
 * @method getOrder(): int
 * @method setOrder(int $itemId): void
 * @method getValue(): string
 * @method setValue(string $value): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 */
class ItemFieldValue extends Entity implements JsonSerializable {
	protected int $itemId = 0;
	protected int $fieldId = 0;
	protected int $order = 0;
	protected string $value = '';
	protected string $userId = '';

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'itemId' => $this->itemId,
			'fieldId' => $this->fieldId,
			'value' => $this->value,
			'userId' => $this->userId
		];
	}
}
