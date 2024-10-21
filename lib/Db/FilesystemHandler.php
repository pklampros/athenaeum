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
		$this->mainFolderName = "Athenaeum";
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

	public function getItemDataFolder($userId, $itemId) : Folder {
		$allItemDataFolder = $this->getAllItemDataFolder($userId);
		return $this->getOrCreateSubFolder($allItemDataFolder, $itemId);
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
			} catch(\OCP\Files\NotFoundException $e) {
				// folder not found, try to create it
				$rootFolder->newFolder($newFolderName);
				$newFolder = $rootFolder->get($newFolderName);
			}
			if ($newFolder instanceof \OCP\Files\File) {
				throw new StorageException('Can not access folder ' .
										$newFolderName . ' because it is a file');
			}
		} catch(\OCP\Files\NotPermittedException $e) {
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
