<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
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

			// in order to be able to plug in different storage backends like files
		// for instance it is a good idea to turn storage related exceptions
		// into service related exceptions so controllers and service users
		// have to deal with only one type of exception
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $title, int $itemTypeId, \DateTime $dateAdded,
						   \DateTime $dateModified, string $userId): Item {
		$item = new Item();
		$item->setTitle($title);
		$item->setItemTypeId($itemTypeId);
		$currentDate = new \DateTime;
		$item->setDateAdded($currentDate);
		$item->setDateModified($currentDate);
		$item->setUserId($userId);
		return $this->mapper->insert($item);
	}

	public function update(int $id, string $title, int $itemTypeId, \DateTime $dateAdded,
						   \DateTime $dateModified, string $userId): Item {
		try {
			$item = $this->mapper->find($id, $userId);
			$item->setTitle($title);
			$item->setItemTypeId($itemTypeId);
			$currentDate = new \DateTime;
			$item->setDateModified($currentDate);
			$item->setUserId($userId);
			return $this->mapper->update($item);
		$item->setUserId($userId);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id, string $userId): Item {
		try {
			$item = $this->mapper->find($id, $userId);
			$this->mapper->delete($item);
			return $item;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
