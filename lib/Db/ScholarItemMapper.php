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
		parent::__construct($db, 'athm_schlr_items', ScholarItem::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id, string $userId): ScholarItem {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_schlr_items')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByUrl(string $url): ScholarItem {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_schlr_items')
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
		   ->innerJoin("ei", "athm_schlr_items", "i", "i.id = ei.scholar_item_id")
		   ->where($qb->expr()->eq('i.user_id', $qb->createNamedParameter($userId)))
		   ->andWhere($qb->expr()->eq('i.read', $qb->createNamedParameter(0)))
		   ->addGroupBy('i.id', 'DESC')
		   ->addOrderBy('alert_importance', 'DESC')
		   ->setFirstResult($offset)
		   ->setMaxResults($limit);
		return $this->findEntities($qb);
	}
}
