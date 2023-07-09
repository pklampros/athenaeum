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
 * @template-extends QBMapper<ItemFieldValue>
 */
class ItemFieldValueMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'athm_item_field_values', ItemFieldValue::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): ItemFieldValue {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_item_field_values')
			->where($qb->expr()
					   ->eq('id',
					    	$qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByFieldId(int $itemId, int $fieldId, int $order): ItemFieldValue {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_item_field_values')
			->where($qb->expr()
					   ->eq('item_id',
					    	$qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()
						  ->eq('field_id',
							   $qb->createNamedParameter($fieldId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()
						  ->eq('order',
							   $qb->createNamedParameter($order, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * @throws DoesNotExistException
	 */
	public function findAllByFieldId(int $itemId, int $fieldId): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_item_field_values')
			->where($qb->expr()
					   ->eq('item_id',
					    	$qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()
						  ->eq('field_id',
							   $qb->createNamedParameter($fieldId, IQueryBuilder::PARAM_INT)));
		return $this->findEntities($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByFieldName(int $itemId, string $fieldName, int $order): ItemFieldValue {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_item_field_values', "ifv")
			->innerJoin("ifv", "athm_fields", "f", "f.id = ifv.field_id")
			->where($qb->expr()
					   ->eq('ifv.item_id',
					    	$qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()
						  ->eq('f.field_name',
							   $qb->createNamedParameter($fieldId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()
						  ->eq('ifv.order',
							   $qb->createNamedParameter($order, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByItemIdFieldId(int $itemId, int $fieldId): ItemFieldValue {
		return $this->find($itemId, $fieldId, 0);
	}
}
