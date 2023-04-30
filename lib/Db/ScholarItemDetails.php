<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class ScholarItemDetails extends Entity implements JsonSerializable {
	protected ?ScholarItem $scholarItem = null;
	protected Array $alertExcerpts = [];

	public function jsonSerialize(): array {
		$jsonScholarItem = [];
		if ($this->scholarItem != null) {
			$jsonScholarItem = $this->scholarItem->jsonSerialize();
		}
		return [
			'scholarItem' => $jsonScholarItem,
			'alertExcerpts' => $this->alertExcerpts
		];
	}
}
