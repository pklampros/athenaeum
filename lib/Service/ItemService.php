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

class ItemService {
	private ItemMapper $mapper;

	public function __construct(ItemMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<Item>
	 */
	public function findAll(string $userId): array {
		return $this->mapper->findAll($userId);
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

	public function findByFieldValue(string $fieldName, string $fieldValue, string $userId): Item {
		try {
			return $this->mapper->findByFieldValue($fieldName, $fieldValue, $userId);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $title, int $itemTypeId, \DateTime $dateAdded,
	                       \DateTime $dateModified, string $userId): Item {
		try {
			$entity = new Item();
			$entity->setTitle($title);
			$entity->setItemTypeId($itemTypeId);
			$currentDate = new \DateTime;
			$entity->setDateAdded($currentDate);
			$entity->setDateModified($currentDate);
			$entity->setUserId($userId);
			return $this->mapper->insert($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function createWithUrl(string $title, int $itemTypeId, \DateTime $dateAdded,
	                       \DateTime $dateModified, string $url, string $userId): Item {
		try {
			$entity = new Item();
			$entity->setTitle($title);
			$entity->setItemTypeId($itemTypeId);
			$currentDate = new \DateTime;
			$entity->setDateAdded($currentDate);
			$entity->setDateModified($currentDate);
			$entity->setUserId($userId);
			return $this->mapper->insertWithUrl($entity, $url);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
	

	public function update(int $id, string $title, int $itemTypeId, \DateTime $dateAdded,
	                       \DateTime $dateModified, string $userId): Item {
		try {
			$entity = $this->mapper->find($id, $userId);
			$entity->setTitle($title);
			$entity->setItemTypeId($itemTypeId);
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
