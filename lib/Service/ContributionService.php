<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Service;

use Exception;

use OCA\Athenaeum\Db\Contribution;
use OCA\Athenaeum\Db\ContributionMapper;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

class ContributionService {
	private ContributionMapper $mapper;

	public function __construct(ContributionMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<Contribution>
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
			throw new ContributionNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id): Contribution {
		try {
			return $this->mapper->find($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function findByItemContributor(string $itemId, string $contributorId): Contribution {
		try {
			return $this->mapper->findByItemContributor($itemId, $contributorId);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(int $itemId, int $contributorId,
		string $contributorNameDisplay, int $contributionTypeId,
		int $contributionOrder): Contribution {
		try {
			$entity = new Contribution();
			$entity->setItemId($itemId);
			$entity->setContributorId($contributorId);
			$entity->setContributorNameDisplay($contributorNameDisplay);
			$entity->setContributionTypeId($contributionTypeId);
			$entity->setContributionOrder($contributionOrder);
			return $this->mapper->insert($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $id, int $itemId, int $contributorId,
		string $contributorNameDisplay, int $contributionTypeId,
		int $contributionOrder): Contribution {
		try {
			$entity = $this->mapper->find($id);
			$entity->setItemId($itemId);
			$entity->setContributorId($contributorId);
			$entity->setContributorNameDisplay($contributorNameDisplay);
			$entity->setContributionTypeId($contributionTypeId);
			$entity->setContributionOrder($contributionOrder);
			return $this->mapper->update($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id): Contribution {
		try {
			$entity = $this->mapper->find($id);
			$this->mapper->delete($entity);
			return $entity;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
