<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Athenaeum\Db\Source;
use OCA\Athenaeum\Db\SourceMapper;

class SourceService {
	private SourceMapper $mapper;

	public function __construct(SourceMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<Source>
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
			throw new SourceNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id, string $userId): Source {
		try {
			return $this->mapper->find($id, $userId);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $uid, string $sourceType,
						   int $importance, string $title, 
						   string $description, string $userId): Source {
		try {
			$entity = new Source();
			$entity->setUid($uid);
			$entity->setSourceType($sourceType);
			$entity->setImportance($importance);
			$entity->setTitle($title);
			$entity->setDescription($description);
			$entity->setUserId($userId);
			return $this->mapper->insert($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $id, int $importance, string $title, 
						   string $description, string $userId): Source {
		try {
			$entity = $this->mapper->find($id, $userId);
			$entity->setImportance($importance);
			$entity->setTitle($title);
			$entity->setDescription($description);
			return $this->mapper->update($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id): Source {
		try {
			$entity = $this->mapper->find($id);
			$this->mapper->delete($entity);
			return $entity;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
