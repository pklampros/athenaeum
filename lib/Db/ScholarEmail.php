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
 * @method getContent(): string
 * @method setContent(string $content): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 */
class ScholarEmail extends Entity implements JsonSerializable {
	protected string $subject = '';
	protected $received;
	protected string $fromAddress = '';
	protected string $toAddress = '';
	protected int $alertId = 0;

	public function __construct() {
		$this->addType('received', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'subject' => $this->subject,
			'received' => $this->received,
			'fromAddress' => $this->fromAddress,
			'toAddress' => $this->toAddress,
			'alertId' => $this->alertId
		];
	}
}
