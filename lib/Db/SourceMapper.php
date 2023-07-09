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
 * @template-extends QBMapper<Source>
 */
class SourceMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'athm_sources', Source::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): Source {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_sources')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}
	
	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByUid(string $uid): Source {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_sources')
			->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid)));
		return $this->findEntity($qb);
	}

	/**
	 * @return array
	 */
	public function findAll(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_sources');
		return $this->findEntities($qb);
	}

	public function getOrInsertByUid(string $uid, string $sourceType): Source {
		try {
			$source = $this->findByUid($uid);
		} catch (DoesNotExistException $e) {
			$entity = new Source();
			$entity->setUid($uid);
			$entity->setSourceType($sourceType);
			$source = $this->insert($entity);
		}
		return $source;
	}
}
