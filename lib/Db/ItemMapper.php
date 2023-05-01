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
	public function findAll(
		string $userId,
		int $limit = 50,
		int $offset = 0,
		?bool $showAll = false,
		string $search = ''
	): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_items')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->setFirstResult($offset)
			->setMaxResults($limit);
		return $this->findEntities($qb);
	}

	private function findFieldId(string $fieldName): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select('id')
		  ->from('athm_fields')
		  ->where($qb->expr()->eq('name', $qb->createNamedParameter($fieldName)));
		return $this->findEntity($qb)->id;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	private function insertItemFieldValue(int $itemId, string $fieldName, $value) {
		$fieldID = $this->findFieldId($fieldName);
		$qb = $this->db->getQueryBuilder();
		$qb->insert('athm_item_field_values')
			->setValue('item_id', $qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT))
			->setValue('field_id', $qb->createNamedParameter($fieldID, IQueryBuilder::PARAM_INT))
			->setValue('order', 0)
			->setValue('value', $qb->createNamedParameter($value));
		$qb->executeStatement();
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	private function insertContributor(string $firstName, string $lastName,
			bool $lastNameIsFullName): int {
		$qb = $this->db->getQueryBuilder();
		$qb->insert('athm_contributors')
			->setValue('first_name', $qb->createNamedParameter($firstName))
			->setValue('last_name', $qb->createNamedParameter($lastName))
			->setValue('last_name_is_full_name', $qb->createNamedParameter($lastNameIsFullName));
		$qb->executeStatement();
		return $qb->getLastInsertId();
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	private function insertContribution(int $itemId, int $contributorId,
			string $contributorDisplayName, int $contributionTypeId,
			int $contributionOrder): int {
		$qb = $this->db->getQueryBuilder();
		$qb->insert('athm_contributions')
			->setValue(
				'item_id',
				$qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT))
			->setValue(
				'contributor_id',
				$qb->createNamedParameter($contributorId, IQueryBuilder::PARAM_INT))
			->setValue(
				'contributor_name_display',
				$qb->createNamedParameter($contributorDisplayName))
			->setValue(
				'contribution_type_id',
				$qb->createNamedParameter($contributionTypeId))
			->setValue(
				'contribution_order',
				$qb->createNamedParameter($contributionOrder));
		$qb->executeStatement();
		return $qb->getLastInsertId();
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function insertDetailed(array $itemData, \DateTime $dateAdded,
	\DateTime $dateModified, string $userId): Item {
        return $this->atomic(function () use (&$itemData, &$dateAdded, &$dateModified, &$userId) {
			$item = new Item();
			$item->setTitle($itemData['title']);
			$item->setItemTypeId(1);
			$item->setDateAdded($dateAdded);
			$item->setDateModified($dateModified);
			$item->setUserId($userId);

			$newItem = $this->insert($item);
			if (array_key_exists('url', $itemData)) {
				$fieldID = $this->insertItemFieldValue($newItem->id, 'url', $itemData['url']);
			}
			if (array_key_exists('authorList', $itemData)) {
				foreach ($itemData['authorList'] as $index=>$author) {
					
					$newContributorId = $this->insertContributor(
						$author['firstName'], $author['name'],
						$author['onlyLastName']);
					
					$this->insertContribution($newItem->id, $newContributorId,
						$author['displayName'], 1, $index + 1);
				}
			}
			return $newItem;
        }, $this->db);
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
			   ->setValue('item_id', $qb->createNamedParameter($newItem->id, IQueryBuilder::PARAM_INT))
			   ->setValue('field_id', $qb->createNamedParameter($fieldID, IQueryBuilder::PARAM_INT))
			   ->setValue('order', 0)
			   ->setValue('value', $qb->createNamedParameter($url));
			$qb->executeStatement();
			return $newItem;
        }, $this->db);
	}
}
