<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Athenaeum\Db\ScholarItem;
use OCA\Athenaeum\Db\ScholarItemMapper;
use OCA\Athenaeum\Db\ScholarItemDetails;

class ScholarItemService {
	private ScholarItemMapper $mapper;

	public function __construct(ScholarItemMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<ScholarItem>
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
			throw new ScholarItemNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id, string $userId): ScholarItem {
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

	public function getWithDetails(int $id, string $userId): ScholarItemDetails {
		try {
			return $this->mapper->getWithDetails($id, $userId);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function findByUrl(string $url): ScholarItem {
		try {
			return $this->mapper->findByUrl($url);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $url, string $title, string $authors,
						   string $journal, string $published, bool $read,
						   int $importance, bool $needsReview, string $userId): ScholarItem {
		try {
			$entity = new ScholarItem();
			$entity->setUrl($url);
			$entity->setTitle($title);
			$entity->setAuthors($authors);
			$entity->setJournal($journal);
			$entity->setPublished($published);
			$entity->setRead($read);
			$entity->setImportance($importance);
			$entity->setNeedsReview($needsReview);
			$entity->setUserId($userId);
			return $this->mapper->insert($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function createFromEML(array $emlData, string $userId): array {
		try {
			return $this->mapper->createFromEML($emlData, $userId);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $id, string $url, string $title, string $authors,
						   string $journal, string $published, bool $read,
						   int $importance, bool $needsReview, string $userId): ScholarItem {
		try {
			$entity = $this->mapper->find($id, $userId);
			$entity->setUrl($url);
			$entity->setTitle($title);
			$entity->setAuthors($authors);
			$entity->setJournal($journal);
			$entity->setPublished($published);
			$entity->setRead($read);
			$entity->setImportance($importance);
			$entity->setNeedsReview($needsReview);
			$entity->setUserId($userId);
			return $this->mapper->update($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id, string $userId): ScholarItem {
		try {
			$entity = $this->mapper->find($id, $userId);
			$this->mapper->delete($entity);
			return $entity;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
