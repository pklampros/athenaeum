<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Athenaeum\Db\Folder;
use OCA\Athenaeum\Db\FolderMapper;

class FolderService {
	private FolderMapper $mapper;

	public function __construct(FolderMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<Folder>
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
			throw new FolderNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id): Folder {
		try {
			return $this->mapper->find($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $path, string $name,
						   bool $editable, string $icon,
						   string $userId): Folder {
		try {
			$entity = new Folder();
			$entity->setPath($path);
			$entity->setName($name);
			$entity->setEditable($editable);
			$entity->setIcon($icon);
			$entity->setUserId($userId);
			return $this->mapper->insert($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $id, string $path, string $name,
	                       bool $editable, string $icon): Folder {
		try {
			$entity = $this->mapper->find($id);
			$entity->setPath($path);
			$entity->setName($name);
			$entity->setEditable($editable);
			$entity->setIcon($icon);
			return $this->mapper->update($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id): Folder {
		try {
			$entity = $this->mapper->find($id);
			$this->mapper->delete($entity);
			return $entity;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
