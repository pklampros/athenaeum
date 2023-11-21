<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros <email@email.email>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Service;

use Exception;

use OCA\Athenaeum\Db\ItemFieldValue;
use OCA\Athenaeum\Db\ItemFieldValueMapper;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

class ItemFieldValueService {
	private ItemFieldValueMapper $mapper;

	public function __construct(ItemFieldValueMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<ItemFieldValue>
	 */
	public function findAll(): array {
		return $this->mapper->findAll();
	}

	/**
	 * @return never
	 */
	private function handleException(Exception $e) {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new ItemFieldValueNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id): ItemFieldValue {
		try {
			return $this->mapper->find($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function findByFieldId(int $itemId, int $fieldId, int $order): ItemFieldValue {
		try {
			return $this->mapper->findByFieldId($itemId, $fieldId, $order);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function findByFieldName(int $itemId, string $fieldName, int $order): ItemFieldValue {
		try {
			return $this->mapper->findByFieldName($itemId, $fieldName, $order);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(int $itemId, int $fieldId, int $order,
		string $value): ItemFieldValue {
		try {
			$entity = new ItemFieldValue();
			$entity->setItemId($itemId);
			$entity->setFieldId($fieldId);
			$entity->setOrder($order);
			$entity->setValue($value);
			return $this->mapper->insert($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $itemId, int $fieldId, int $order,
		string $value): ItemFieldValue {
		try {
			$entity = $this->mapper->find($id);
			$entity->setItemId($itemId);
			$entity->setFieldId($fieldId);
			$entity->setOrder($order);
			$entity->setValue($value);
			return $this->mapper->update($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id): ItemFieldValue {
		try {
			$item = $this->mapper->find($id);
			$this->mapper->delete($item);
			return $item;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
