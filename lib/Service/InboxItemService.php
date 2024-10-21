<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Service;

use Exception;

use OCA\Athenaeum\Db\InboxItem;
use OCA\Athenaeum\Db\InboxItemDetails;

use OCA\Athenaeum\Db\Item;
use OCA\Athenaeum\Db\ItemMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IConfig;

class InboxItemService {
	private ItemMapper $mapper;

	public function __construct(private IConfig $config, ItemMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<InboxItem>
	 */
	public function findAll(
		string $userId,
		string $folder = "",
		int $limit = 50,
		int $offset = 0,
		?bool $showAll = false,
		string $search = ''
	): array {
		$fullFolder = "inbox";
		if ($folder) {
			$fullFolder = $fullFolder . "/" . $folder;
		}
		return $this->mapper->findAll(
			$userId, $this->mapper->findFolderId($fullFolder), $limit, $offset, $showAll, $search
		);
	}

	/**
	 * @return never
	 */
	private function handleException(Exception $e) {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new InboxItemNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id, string $userId): InboxItem {
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

	public function getWithDetails(int $id, string $userId): InboxItemDetails {
		try {
			return $this->mapper->getInboxItemDetails($id, $userId);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function decideLater(int $id, string $userId): bool {
		try {
			$this->mapper->decideLater($id, $userId);
			return true;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function findByUrl(string $url): InboxItem {
		try {
			return $this->mapper->findByUrl($url);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $url, string $title, string $authors,
		string $journal, string $published, bool $read,
		int $importance, bool $needsReview, string $userId): InboxItem {
		try {
			$entity = new InboxItem();
			$entity->setUrl($url);
			$entity->setTitle($title);
			$entity->setAuthors($authors);
			$entity->setJournal($journal);
			$entity->setPublished($published);
			$entity->setRead($read);
			$entity->setImportance($importance);
			$entity->setNeedsReview($needsReview);
			$entity->setUserId($userId);
			return $this->mapper->insertItem($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function toLibrary(int $id, array $itemData, \DateTime $dateAdded,
		\DateTime $dateModified, string $userId): Item {
		try {
			return $this->mapper->inboxToLibrary(
				$id, $itemData, $dateAdded,
				$dateModified, $userId
			);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function createFromEML(array $emlData, string $userId): array {
		try {
			$currentDate = new \DateTime;
			return $this->mapper->createFromEML(
				$emlData, $currentDate, $currentDate, $userId
			);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $id, string $url, string $title, string $authors,
		string $journal, string $published, bool $read,
		int $importance, bool $needsReview, string $userId): InboxItem {
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
			return $this->mapper->updateItem($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id, string $userId): InboxItem {
		try {
			$entity = $this->mapper->find($id, $userId);
			$this->mapper->delete($entity);
			return $entity;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
