<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use OCP\AppFramework\Db\DoesNotExistException;
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
	
	public function __construct(IDBConnection $db, IRootFolder $storage) {
		parent::__construct($db, 'athm_item_attchm', ItemAttachment::class);
		$this->storage = $storage;
	}


	public function createAttachmentFile($itemId, string $fileName, $fileData, string $userId) : string {
		$fsh = new FilesystemHandler($this->storage);
		$itemAttachmentsFolder = $fsh->getItemAttachmentsFolder($userId, $itemId);
		try {
			try {
				$itemAttachmentsFolder->get($fileName);
				throw new FileExistsException('File already exists: ' . $fileName);
			} catch(\OCP\Files\NotFoundException $e) {
				// does not exist, continue
			}
			$itemAttachmentsFolder->newFile($fileName, $fileData);
			$mainFolder = $fsh->getMainFolder($userId);
			$finalFile = $itemAttachmentsFolder->get($fileName)->getPath();
			return $mainFolder->getRelativePath($finalFile);
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
			->where($qb->expr()->eq('item_id',
				$qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id',
				$qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)));
		return $this->findEntities($qb);
	}

	public function pathExists(int $itemId, string $path): bool {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->selectAlias($qb->createFunction('COUNT(`id`)'), 'count')
			->from('athm_item_attchm')
			->where($qb->expr()->eq('item_id',
				$qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('path',
				$qb->createNamedParameter($path)));
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
			->where($qb->expr()->eq('item_id',
				$qb->createNamedParameter($item_id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('path',
				$qb->createNamedParameter($path)));
		return $this->findEntity($qb);
	}
}
