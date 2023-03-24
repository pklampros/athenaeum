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
 * @template-extends QBMapper<ScholarAlert>
 */
class ScholarAlertMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'athm_schlr_alerts', ScholarAlert::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): ScholarAlert {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_schlr_alerts')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}
	
	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByScholarId(string $scholarId): ScholarAlert {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_schlr_alerts')
			->where($qb->expr()->eq('scholar_id', $qb->createNamedParameter($scholarId)));
		return $this->findEntity($qb);
	}

	/**
	 * @return array
	 */
	public function findAll(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_schlr_alerts');
		return $this->findEntities($qb);
	}
}
