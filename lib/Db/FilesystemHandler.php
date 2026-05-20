<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use OCP\Files\Folder;
use OCP\Files\IRootFolder;

class FilesystemHandler {
	private IRootFolder $storage;
	private string $mainFolderName;

	public function __construct(IRootFolder $storage) {
		$this->storage = $storage;
		$this->mainFolderName = 'Athenaeum';
	}

	private function getUserFolder($userId) : Folder {
		return $this->storage->getUserFolder($userId);
	}

	public function getMainFolder($userId) : Folder {
		$userFolder = $this->getUserFolder($userId);
		return $this->getOrCreateSubFolder($userFolder, $this->mainFolderName);
	}

	private function getAllItemDataFolder($userId) : Folder {
		$mainFolder = $this->getMainFolder($userId);
		return $this->getOrCreateSubFolder($mainFolder, 'itemdata');
	}

	private function shardComponents(int $itemId): array {
		$level1 = sprintf('%02d', intdiv($itemId, 1000) % 100);
		$level2 = sprintf('%02d', intdiv($itemId, 10) % 100);
		return [$level1, $level2];
	}

	public function getItemDataFolder($userId, $itemId) : Folder {
		$allItemDataFolder = $this->getAllItemDataFolder($userId);
    	[$level1, $level2] = $this->shardComponents((int)$itemId);

		// New layout: itemdata/XX/YY/<id>/
		if ($allItemDataFolder->nodeExists("$level1/$level2/$itemId")) {
			$node = $allItemDataFolder->get("$level1/$level2/$itemId");
			if ($node instanceof Folder) {
				return $node;
			}
		}

		// Old (flat) layout: itemdata/<id>/  — read-only, don't create here
		if ($allItemDataFolder->nodeExists((string)$itemId)) {
			$node = $allItemDataFolder->get((string)$itemId);
			if ($node instanceof Folder) {
				return $node;
			}
		}
		
    	// Doesn't exist anywhere — create in new layout
		$level1Folder = $this->getOrCreateSubFolder($allItemDataFolder, $level1);
		$level2Folder = $this->getOrCreateSubFolder($level1Folder, $level2);
		return $this->getOrCreateSubFolder($level2Folder, (string)$itemId);
	}

	private function getAllContributorsDataFolder($userId) : Folder {
		$mainFolder = $this->getMainFolder($userId);
		return $this->getOrCreateSubFolder($mainFolder, 'contributordata');
	}

	public function getContributorsDataFolder($userId, $id) : Folder {
		$allItemDataFolder = $this->getAllContributorsDataFolder($userId);
		return $this->getOrCreateSubFolder($allItemDataFolder, $id);
	}

	private function getAllSourcesDataFolder($userId) : Folder {
		$mainFolder = $this->getMainFolder($userId);
		return $this->getOrCreateSubFolder($mainFolder, 'sourcedata');
	}

	public function getSourcesDataFolder($userId, $id) : Folder {
		$allItemDataFolder = $this->getAllSourcesDataFolder($userId);
		return $this->getOrCreateSubFolder($allItemDataFolder, $id);
	}

	private function getOrCreateSubFolder($rootFolder, $newFolderName) : Folder {
		try {
			try {
				$newFolder = $rootFolder->get($newFolderName);
			} catch (\OCP\Files\NotFoundException $e) {
				// folder not found, try to create it
				$rootFolder->newFolder($newFolderName);
				$newFolder = $rootFolder->get($newFolderName);
			}
			if ($newFolder instanceof \OCP\Files\File) {
				throw new StorageException('Can not access folder ' .
										$newFolderName . ' because it is a file');
			}
		} catch (\OCP\Files\NotPermittedException $e) {
			// can not access or create folder
			throw new StorageException('Cant access or create folder ' .
									   $newFolderName);
		}
		return $newFolder;
	}

	public function getItemAttachmentsFolder($userId, $itemId) : Folder {
		$itemDataFolder = $this->getItemDataFolder($userId, $itemId);
		return $this->getOrCreateSubFolder($itemDataFolder, 'attachments');
	}
}
