<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Service;

use Exception;

use OCA\Athenaeum\Db\Contributor;
use OCA\Athenaeum\Db\ContributorMapper;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

class ContributorService {
	private ContributorMapper $mapper;

	public function __construct(ContributorMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<Contributor>
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
			throw new ContributorNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id): Contributor {
		try {
			return $this->mapper->find($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function findSimilar(string $firstName, string $lastName,
		string $displayName): array {
		try {
			return $this->mapper->findSimilar($firstName, $lastName, $displayName);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function freeSearch(string $term): array {
		try {
			return $this->mapper->freeSearch($term);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function findByFirstLastName(string $firstName, string $lastName): Contributor {
		try {
			return $this->mapper->findByFirstLastName($firstName, $lastName);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $firstName, string $lastName,
		bool $lastNameIsFullName): Contributor {
		try {
			$entity = new Contributor();
			$entity->setFirstName($firstName);
			$entity->setLastName($lastName);
			$entity->setLastNameIsFullName($lastNameIsFullName);
			$currentDate = new \DateTime;
			$contributor->setDateAdded($currentDate);
			$contributor->setDateModified($currentDate);
			return $this->mapper->insertContributor($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $id, string $firstName, string $lastName,
		bool $lastNameIsFullName): Contributor {
		try {
			$entity = $this->mapper->find($id);
			$entity->setFirstName($firstName);
			$entity->setLastName($lastName);
			$entity->setLastNameIsFullName($lastNameIsFullName);
			$currentDate = new \DateTime;
			$contributor->setDateModified($currentDate);
			return $this->mapper->updateContributor($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id): Contributor {
		try {
			$entity = $this->mapper->find($id);
			$this->mapper->delete($entity);
			return $entity;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
