<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class InboxItemDetails extends Entity implements JsonSerializable {
	protected ?InboxItem $inboxItem = null;
	protected array $sourceInfo = [];

	public function jsonSerialize(): array {
		$jsonInboxItem = [];
		if ($this->inboxItem != null) {
			$jsonInboxItem = $this->inboxItem->jsonSerialize();
		}
		return [
			'inboxItem' => $jsonInboxItem,
			'sourceInfo' => $this->sourceInfo
		];
	}
}
