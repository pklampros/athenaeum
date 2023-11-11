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
	public function find(int $id, string $userId): Source {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_sources')
			->where($qb->expr()->eq('id',
				$qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id',
				$qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)));
		return $this->findEntity($qb);
	}
	
	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByUid(string $uid, string $userId): Source {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_sources')
			->where($qb->expr()->eq('uid',
				$qb->createNamedParameter($uid)))
			->andWhere($qb->expr()->eq('user_id',
				$qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)));
		return $this->findEntity($qb);
	}

	/**
	 * @return array
	 */
	public function findAll(string $userId): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_sources')
			->where($qb->expr()->eq('user_id',
				$qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)));
		return $this->findEntities($qb);
	}

	public function getOrInsertByUid(string $uid, string $sourceType,
									 int $importance, string $title, 
									 string $description, string $userId): Source {
		try {
			$source = $this->findByUid($uid, $userId);
		} catch (DoesNotExistException $e) {
			$entity = new Source();
			$entity->setUid($uid);
			$entity->setSourceType($sourceType);
			$entity->setImportance($importance);
			$entity->setTitle($title);
			$entity->setDescription($description);
			$entity->setUserId($userId);
			$source = $this->insert($entity);
		}
		return $source;
	}
}
