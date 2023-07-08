<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<ScholarItem>
 */
class ScholarItemMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'athm_inbox_items', ScholarItem::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id, string $userId): ScholarItem {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_inbox_items')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function getWithDetails(int $id, string $userId): ScholarItemDetails {
		$scholarEmailItemMapper = new ScholarEmailItemMapper($this->db);
		$sifs = new ScholarItemDetails();
		$sifs->setScholarItem($this->find($id, $userId));
		$qb = $this->db->getQueryBuilder();
		$qb->select('ei.excerpt')
			->addSelect('a.term')
			->addSelect('a.importance')
		   ->from('athm_schlr_email_items', 'ei')
		   ->innerJoin("ei", "athm_schlr_emails", "e", "e.id = ei.scholar_email_id")
		   ->innerJoin("e", "athm_schlr_alerts", "a", "a.id = e.scholar_alert_id")
			->where($qb->expr()->eq('ei.scholar_item_id',
									$qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		
		$excerpts = [];
		$result = $qb->executeQuery();
		try {
			while ($row = $result->fetch()) {
				array_push($excerpts, $row);
			}
		} finally {
			$result->closeCursor();
		}
		$sifs->setAlertExcerpts($excerpts);
		return $sifs;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByUrl(string $url): ScholarItem {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_inbox_items')
			->where($qb->expr()->eq('url', $qb->createNamedParameter($url)));
		return $this->findEntity($qb);
	}

	/**
	 * @param string $userId
	 * @return array
	 */
	public function findAll(
		string $userId,
		int $limit = 50,
        int $offset = 0,
        ?bool $showAll = false,
        string $search = ''
	): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('i.*')
		   ->addSelect($qb->createFunction('SUM(a.importance) AS alert_importance'))
		   ->from('athm_schlr_email_items', 'ei')
		   ->innerJoin("ei", "athm_schlr_emails", "e", "e.id = ei.scholar_email_id")
		   ->innerJoin("e", "athm_schlr_alerts", "a", "a.id = e.scholar_alert_id")
		   ->innerJoin("ei", "athm_inbox_items", "i", "i.id = ei.scholar_item_id")
		   ->where($qb->expr()->eq('i.user_id', $qb->createNamedParameter($userId)))
		   ->andWhere($qb->expr()->eq('i.read', $qb->createNamedParameter(0)))
		   ->addGroupBy('i.id', 'DESC')
		   ->addOrderBy('alert_importance', 'DESC')
		   ->setFirstResult($offset)
		   ->setMaxResults($limit);
		return $this->findEntities($qb);
	}

	private function getOrInsertScholarAlert(string $scholarId, string $term): ScholarAlert {
		$scholarAlertMapper = new ScholarAlertMapper($this->db);
		try {
			$alert = $scholarAlertMapper->findByScholarId($scholarId);
		} catch (DoesNotExistException $e) {
			$entity = new ScholarAlert();
			$entity->setScholarId($scholarId);
			$entity->setTerm($term);
			$alert = $scholarAlertMapper->insert($entity);
		}
		return $alert;
	}

	private function getOrInsertScholarEmail(string $subject, \DateTime $received,
											 int $scholarAlertId): ScholarEmail {
		$scholarEmailMapper = new ScholarEmailMapper($this->db);
		try {
			$email = $scholarEmailMapper->findBySubjectReceived($subject, $received);
		} catch (DoesNotExistException $e) {
			$entity = new ScholarEmail();
			$entity->setSubject($subject);
			$entity->setReceived($received);
			$entity->setScholarAlertId($scholarAlertId);
			$email = $scholarEmailMapper->insert($entity);
		}
		return $email;
	}

	private function getOrInsertScholarItem(string $url, string $title, string $authors,
											string $journal, string $published, bool $read,
											int $importance, bool $needsReview,
											string $userId): ScholarItem {
		try {
			$item = $this->findByUrl($url);
		} catch (DoesNotExistException $e) {
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
			$item = $this->insert($entity);
		}
		return $item;
	}

	private function getOrInsertScholarEmailItem(int $scholarEmailId, int $scholarItemId,
												 string $excerpt): ScholarEmailItem {
		$scholarEmailItemMapper = new ScholarEmailItemMapper($this->db);
		try {
			$emailItem = $scholarEmailItemMapper->findByEmailItem($scholarEmailId, $scholarItemId);
		} catch (DoesNotExistException $e) {
			$entity = new ScholarEmailItem();
			$entity->setScholarEmailId($scholarEmailId);
			$entity->setScholarItemId($scholarItemId);
			$entity->setExcerpt($excerpt);
			$emailItem = $scholarEmailItemMapper->insert($entity);
		}
		return $emailItem;
	}

	public function createFromEML(array $emlData, string $userId): array {
		$this->db->beginTransaction();
		try {
			$qb = $this->db->getQueryBuilder();

			$alert = $this->getOrInsertScholarAlert($emlData["alertID"], $emlData["searchTerm"]);
			$email = $this->getOrInsertScholarEmail($emlData["subject"], $emlData["received"], $alert->id);
			
			foreach ($emlData["items"] as $emlItem) {
				$item = $this->getOrInsertScholarItem($emlItem["url"], $emlItem["title"], $emlItem["authors"],
													  $emlItem["journal"], $emlItem["published"], false,
													  0, false, $userId);
				$emailItem = $this->getOrInsertScholarEmailItem($email->id, $item->id, $emlItem["excerpt"]);
			}
			$this->db->commit();
			
			return array();
		} catch (Throwable $e) {
			$this->db->rollBack();
			throw $e;
		}
	}
}
