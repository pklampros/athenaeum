<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<ItemAttachment>
 */
class ItemAttachmentMapper extends QBMapper {
    private IRootFolder $storage;
	private string $mainFolderName;
	
	public function __construct(IDBConnection $db, IRootFolder $storage) {
		parent::__construct($db, 'athm_item_attchm', ItemAttachment::class);
		$this->storage = $storage;
		$this->mainFolderName = "Athenaeum";
	}

	private function getUserFolder($userId) : Folder {
		return $this->storage->getUserFolder($userId);
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

	private function getAllItemDataFolder($userId) : Folder {
		$userFolder = $this->getUserFolder($userId);
		$mainFolder = $this->getOrCreateSubFolder($userFolder, $this->mainFolderName);
		return $this->getOrCreateSubFolder($mainFolder, 'itemdata');
	}

	private function getItemDataFolder($userId, $itemId) : Folder {
		$allItemDataFolder = $this->getAllItemDataFolder($userId);
		return $this->getOrCreateSubFolder($allItemDataFolder, $itemId);
	}

	public function createAttachmentFile($itemId, string $fileName, $fileData, string $userId) {
		$itemDataFolder = $this->getItemDataFolder($userId, $itemId);
		$itemAttachmentsFolder = $this->getOrCreateSubFolder($itemDataFolder, 'attachments');
		try {
			try {
				$itemAttachmentsFolder->get($fileName);
				throw new FileExistsException('File already exists: ' . $fileName);
			} catch(\OCP\Files\NotFoundException $e) {
				// does not exist, continue
			}
			$itemAttachmentsFolder->newFile($fileName, $fileData);
			return $itemAttachmentsFolder->get($fileName);
		} catch(\OCP\Files\NotPermittedException $e) {
			// can not access or create main folder
			throw new StorageException('Cant access or create file ' . $fileName);
		}
	}

	/**
	 * @throws DoesNotExistException
	 */
	public function findAllByItem(int $itemId, string $userId): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_item_attchm')
			->where($qb->expr()->eq('item_id', $qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT)));
		return $this->findEntities($qb);
	}

	public function pathExists(int $itemId, string $path): bool {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->selectAlias($qb->createFunction('COUNT(`id`)'), 'count')
			->from('athm_item_attchm')
			->where($qb->expr()->eq('item_id', $qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('path', $qb->createNamedParameter($path)));
		$cursor = $qb->execute();
		$row = $cursor->fetch();
		$cursor->closeCursor();
		return $row['count'] == 1;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function getByPath(int $itemId, string $path, string $userId): ItemAttachment {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_item_attchm')
			->where($qb->expr()->eq('item_id', $qb->createNamedParameter($item_id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('path', $qb->createNamedParameter($path)));
		return $this->findEntity($qb);
	}
}
