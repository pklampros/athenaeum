<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<ItemSource>
 */
class ItemSourceMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'athm_item_sources', ItemSource::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): ItemSource {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_item_sources')
			->where($qb->expr()
				->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByItemSource(int $itemId, int $sourceId): ItemSource {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_item_sources')
			->where($qb->expr()
				->eq('item_id',
					$qb->createNamedParameter($itemId,
						IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()
				->eq('source_id',
					$qb->createNamedParameter($sourceId,
						IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 */
	public function itemSourceExists(int $itemId, int $sourceId): bool {
		try {
			$this->findByItemSource($itemId, $sourceId);
		} catch (DoesNotExistException $ie) {
			return false;
		}
		return true;
	}

	/**
	 * @return array
	 */
	public function findAll(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_item_sources');
		return $this->findEntities($qb);
	}
}
