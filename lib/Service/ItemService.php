<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros <email@email.email>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Athenaeum\Db\Item;
use OCA\Athenaeum\Db\ItemMapper;
use OCA\Athenaeum\Db\ItemDetails;

class ItemService {
	private ItemMapper $mapper;

	public function __construct(ItemMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<Item>
	 */
	public function findAll(
		string $userId,
        int $limit = 50,
        int $offset = 0,
        ?bool $showAll = false,
        string $search = ''
	): array {
		return $this->mapper->findAll(
			$userId, $limit, $offset, $showAll, $search
		);
	}

	/**
	 * @return never
	 */
	private function handleException(Exception $e) {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new ItemNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id, string $userId): Item {
		try {
			return $this->mapper->find($id, $userId);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function getWithDetails(int $id, string $userId): ItemDetails {
		try {
			return $this->mapper->getWithDetails($id, $userId);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function findByFieldValue(string $fieldName, string $fieldValue, string $userId): Item {
		try {
			return $this->mapper->findByFieldValue($fieldName, $fieldValue, $userId);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $title, int $itemTypeId, int $folderId,
						   \DateTime $dateAdded, \DateTime $dateModified,
						   string $userId): Item {
		try {
			$entity = new Item();
			$entity->setTitle($title);
			$entity->setItemTypeId($itemTypeId);
			$entity->setFolderId($folderId);
			$currentDate = new \DateTime;
			$entity->setDateAdded($currentDate);
			$entity->setDateModified($currentDate);
			$entity->setUserId($userId);
			return $this->mapper->insert($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function createDetailed(array $itemData, \DateTime $dateAdded,
	                       \DateTime $dateModified, string $userId): Item {
		try {
			return $this->mapper->insertDetailed(
				$itemData, $dateAdded,
				$dateModified, $userId
			);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function createWithUrl(string $title, int $itemTypeId, int $folderId,
						   \DateTime $dateAdded, \DateTime $dateModified, string $url,
						   string $userId): Item {
		try {
			$entity = new Item();
			$entity->setTitle($title);
			$entity->setItemTypeId($itemTypeId);
			$entity->setFolderId($folderId);
			$currentDate = new \DateTime;
			$entity->setDateAdded($currentDate);
			$entity->setDateModified($currentDate);
			$entity->setUserId($userId);
			return $this->mapper->insertWithUrl($entity, $url);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function createInboxItem(string $url, string $title, string $authors,
									string $journal, string $published,
									bool $read, int $importance, bool $needsReview,
									string $userId) : Item {
		try {
			$entity = new Item();
			$entity->setTitle($title);
			$itemTypeId = 1; # paper
			$entity->setItemTypeId($itemTypeId);
			$folderId = 1; # inbox
			$entity->setFolderId($folderId);
			$currentDate = new \DateTime;
			$entity->setDateAdded($currentDate);
			$entity->setDateModified($currentDate);
			$entity->setUserId($userId);
			return $this->mapper->insertAsInboxItem($entity, $url, $authors, $journal,
													$published, $read, $importance,
													$needsReview);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
	

	public function update(int $id, string $title, int $itemTypeId, int $folderId,
						   \DateTime $dateAdded, \DateTime $dateModified,
						   string $userId): Item {
		try {
			$entity = $this->mapper->find($id, $userId);
			$entity->setTitle($title);
			$entity->setItemTypeId($itemTypeId);
			$entity->setFolderId($folderId);
			$currentDate = new \DateTime;
			$entity->setDateModified($currentDate);
			$entity->setUserId($userId);
			return $this->mapper->update($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id, string $userId): Item {
		try {
			$entity = $this->mapper->find($id, $userId);
			$this->mapper->delete($entity);
			return $entity;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
