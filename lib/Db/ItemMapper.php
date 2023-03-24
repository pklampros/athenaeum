<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use OCP\AppFramework\Db\TTransactional;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<Item>
 */
class ItemMapper extends QBMapper {
    use TTransactional;
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'athm_items', Item::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id, string $userId): Item {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_items')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByFieldValue(string $fieldName, string $fieldValue, string $userId): Item {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('i.*')
			->from('athm_items', 'i')
			->where($qb->expr()->eq('i.user_id', $qb->createNamedParameter($userId)))
			->innerJoin("i", "athm_item_field_values", "ifv", "i.id = ifv.item_id")
			->innerJoin("i", "athm_fields", "f", "f.id = ifv.field_id")
			->where($qb->expr()->eq('f.name', $qb->createNamedParameter($fieldName)))
			->andWhere($qb->expr()->eq('ifv.value', $qb->createNamedParameter($fieldValue)));
		return $this->findEntity($qb);
	}

	/**
	 * @param string $userId
	 * @return array
	 */
	public function findAll(string $userId): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_items')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		return $this->findEntities($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function insertWithUrl(Item $item, string $url): Item {
        return $this->atomic(function () use (&$item, &$url) {
			$newItem = $this->insert($item);
			$qb = $this->db->getQueryBuilder();
			$qb->select('id')
			  ->from('athm_fields')
			  ->where($qb->expr()->eq('name', $qb->createNamedParameter('url')));
			$fieldID = $this->findEntity($qb)->id;
			$qb->insert('athm_item_field_values')
			   ->setValue('item_id', '?')
			   ->setValue('field_id', '?')
			   ->setValue('order', '?')
			   ->setValue('value', '?')
			   ->setParameter(0, $newItem->id)
			   ->setParameter(1, $fieldID)
			   ->setParameter(2, 0)
			   ->setParameter(3, $url);
			$qb->executeStatement();
			return $newItem;
        }, $this->db);
	}
}
