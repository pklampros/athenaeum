<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class ItemFileAttachment extends Entity implements JsonSerializable {
	protected ?ItemAttachment $itemAttachment = null;
	protected string $downloadPath = '';
	protected string $openPath = '';
	
	public function jsonSerialize(): array {
		$jsonItemAttachment = [];
		if ($this->itemAttachment != null) {
			$jsonItemAttachment = $this->itemAttachment->jsonSerialize();
		}
		return [
			'itemAttachment' => $jsonItemAttachment,
			'downloadPath' => $this->downloadPath,
			'openPath' => $this->openPath,
		];
	}
}
