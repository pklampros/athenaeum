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
 * @template-extends QBMapper<ItemTag>
 */
class ItemTagMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'athm_item_tags', ItemTag::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $itemId, string $userId): ItemTag {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_item_tags')
			->where($qb->expr()->eq('item_id', $qb->createNamedParameter($item_id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('tag_id', $qb->createNamedParameter($tag_id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}
}
