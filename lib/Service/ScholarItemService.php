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

class ScholarItemService {
	private ScholarItemMapper $mapper;

	public function __construct(ScholarItemMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<ScholarItem>
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

	public function create(string $url, string $title, string $authors,
						   string $journal, string $published, bool $read,
						   int $importance, bool $needsReview, string $userId): ScholarItem {
		try {
			$item = new ScholarItem();
			$item->setUrl($url);
			$item->setTitle($title);
			$item->setAuthors($authors);
			$item->setJournal($journal);
			$item->setPublished($published);
			$item->setRead($read);
			$item->setImportance($importance);
			$item->setNeedsReview($needsReview);
			$item->setUserId($userId);
			return $this->mapper->insert($item);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $id, string $url, string $title, string $authors,
						   string $journal, string $published, bool $read,
						   int $importance, bool $needsReview, string $userId): ScholarItem {
		try {
			$item = $this->mapper->find($id, $userId);
			$item->setUrl($url);
			$item->setTitle($title);
			$item->setAuthors($authors);
			$item->setJournal($journal);
			$item->setPublished($published);
			$item->setRead($read);
			$item->setImportance($importance);
			$item->setNeedsReview($needsReview);
			$item->setUserId($userId);
			return $this->mapper->update($item);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id, string $userId): ScholarItem {
		try {
			$item = $this->mapper->find($id, $userId);
			$this->mapper->delete($item);
			return $item;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
