<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class ItemDetails extends Entity implements JsonSerializable {
	protected ?Item $item = null;
	protected Array $contributions = [];
	protected Array $fieldData = [];
	protected Array $attachments = [];
	protected Array $sourceInfo = [];

	public function jsonSerialize(): array {
		$jsonItem = [];
		if ($this->item != null) {
			$jsonItem = $this->item->jsonSerialize();
		}
		return [
			'item' => $jsonItem,
			'contributions' => $this->contributions,
			'fieldData' => $this->fieldData,
			'attachments' => $this->attachments,
			'sourceInfo' => $this->sourceInfo
		];
	}
}
