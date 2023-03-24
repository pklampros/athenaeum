<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Athenaeum\Db\ScholarEmailItem;
use OCA\Athenaeum\Db\ScholarEmailItemMapper;

class ScholarEmailItemService {
	private ScholarEmailItemMapper $mapper;

	public function __construct(ScholarEmailItemMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<ScholarEmailItem>
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
			throw new ScholarEmailItemNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id): ScholarEmailItem {
		try {
			return $this->mapper->find($id);

			// in order to be able to plug in different storage backends like files
		// for instance it is a good idea to turn storage related exceptions
		// into service related exceptions so controllers and service users
		// have to deal with only one type of exception
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function findByEmailItem(int $scholarEmailId, int $scholarItemId): ScholarEmailItem {
		try {
			return $this->mapper->findByEmailItem($scholarEmailId, $scholarItemId);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(int $scholarEmailId, int $scholarItemId,
						   string $excerpt): ScholarEmailItem {
		try {
			$entity = new ScholarEmailItem();
			$entity->setScholarEmailId($scholarEmailId);
			$entity->setScholarItemId($scholarItemId);
			$entity->setExcerpt($excerpt);
			return $this->mapper->insert($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $id, int $scholarEmailId, int $scholarItemId,
						   string $excerpt): ScholarEmailItem {
		try {
			$entity = $this->mapper->find($id);
			$entity->setScholarEmailId($scholarEmailId);
			$entity->setScholarItemId($scholarItemId);
			$entity->setExcerpt($excerpt);
			return $this->mapper->update($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id): ScholarEmailItem {
		try {
			$entity = $this->mapper->find($id);
			$this->mapper->delete($entity);
			return $entity;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
