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
 * @method getSubject(): string
 * @method setSubject(string $title): void
 * @method getReceived(): \DateTime
 * @method setReceived(\DateTime $received): void
 * @method getFromAddress(): string
 * @method setFromAddress(string $fromAddress): void
 * @method getToAddress(): string
 * @method setToAddress(string $toAddress): void
 * @method getAlertId(): int
 * @method setAlertId(int $alertId): void
 */
class ScholarEmail extends Entity implements JsonSerializable {
	protected string $subject = '';
	protected $received;
	protected string $fromAddress = '';
	protected string $toAddress = '';
	protected int $scholarAlertId = 0;

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
			'scholarAlertId' => $this->scholarAlertId
		];
	}
}
