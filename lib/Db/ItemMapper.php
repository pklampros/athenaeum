<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Db\TTransactional;
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
	public function getWithDetails(int $id, string $userId): ItemDetails {
		$contributionMapper = new ContributionMapper($this->db);
		$contributorMapper = new ContributorMapper($this->db);
		$fieldMapper = new FieldMapper($this->db);

		$itemDetails = new ItemDetails();
		$itemDetails->setItem($this->find($id, $userId));

		$qb = $this->db->getQueryBuilder();
		$qb->select('co.id')
			->addSelect('co.first_name')
			->addSelect('co.last_name')
			->addSelect('co.last_name_is_full_name')
			->addSelect('ci.contribution_order')
			->addSelect('ci.contribution_type_id')
			->addSelect('ci.contributor_name_display')
			->from('athm_contributions', 'ci')
			->innerJoin("ci", "athm_contributors", "co", "co.id = ci.contributor_id")
			->where($qb->expr()->eq('ci.item_id',
									$qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		
		$contributions = [];
		$result = $qb->executeQuery();
		try {
			while ($row = $result->fetch()) {
				array_push($contributions, $row);
			}
		} finally {
			$result->closeCursor();
		}
		$itemDetails->setContributions($contributions);


		$qb = $this->db->getQueryBuilder();
		$qb->select('ifv.order')
			->addSelect('ifv.value')
			->addSelect('f.name')
			->addSelect('f.type_hint')
			->from('athm_fields', 'f')
			->innerJoin("f", "athm_item_field_values", "ifv", "f.id = ifv.field_id")
			->where($qb->expr()->eq('ifv.item_id',
									$qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		
		$fieldData = [];
		$result = $qb->executeQuery();
		try {
			while ($row = $result->fetch()) {
				array_push($fieldData, $row);
			}
		} finally {
			$result->closeCursor();
		}
		$itemDetails->setFieldData($fieldData);

		return $itemDetails;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function getInboxItemDetails(int $id, string $userId): InboxItemDetails {
		$contributionMapper = new ContributionMapper($this->db);
		$contributorMapper = new ContributorMapper($this->db);
		$fieldMapper = new FieldMapper($this->db);

		$item = $this->find($id, $userId);
		$inboxItem = new InboxItem();
		$inboxItem->setTitle($item->title);
		$itemDetails = new InboxItemDetails();
		$itemDetails->setInboxItem($inboxItem);

		$qb = $this->db->getQueryBuilder();
		$qb->select('ifv.order')
			->addSelect('ifv.value')
			->addSelect('f.name')
			->addSelect('f.type_hint')
			->from('athm_fields', 'f')
			->innerJoin("f", "athm_item_field_values", "ifv", "f.id = ifv.field_id")
			->where($qb->expr()->eq('ifv.item_id',
									$qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		
		$result = $qb->executeQuery();
		try {
			while ($fieldData = $result->fetch()) {
				$field = $fieldData["name"];
				$value = $fieldData["value"];
				if ($field == 'url') {
					$inboxItem->setUrl($value);
				} else if ($field == 'inbox_read') {
					$inboxItem->setRead($value);
				} else if ($field == 'inbox_importance') {
					$inboxItem->setImportance($value);
				} else if ($field == 'inbox_needs_review') {
					$inboxItem->setNeedsReview($value);
				} else if ($field == 'inbox_source_data') {
					$sourceData = json_decode($value, true);
					unset($sourceData['sourceId']);
					$itemDetails->setSourceData($sourceData);
				}
			}
		} finally {
			$result->closeCursor();
		}

		$qb = $this->db->getQueryBuilder();
		$qb->select('its.extra')
			->addSelect('s.importance')
			->addSelect('s.source_type')
			->from('athm_item_sources', 'its')
			->innerJoin("its", "athm_sources", "s", "s.id = its.source_id")
			->where($qb->expr()->eq('its.item_id',
									$qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		
		$result = $qb->executeQuery();
		try {
			$sourceInfo = array();
			while ($fieldData = $result->fetch()) {
				$sourceData = json_decode($fieldData["extra"], true);
				unset($sourceData['sourceId']);
				$inboxItem->setAuthors($sourceData['authors']);
				$inboxItem->setJournal($sourceData['journal']);
				$inboxItem->setPublished($sourceData['published']);
				$extra[] = array(
					'importance' => $fieldData['importance'],
					'type' => $fieldData['source_type'],
					'extra' => array(
						'searchTerm' => $sourceData['searchTerm'],
						'excerpt' => $sourceData['excerpt']
					)
				);
			}
			$itemDetails->setSourceInfo($extra);
		} finally {
			$result->closeCursor();
		}

		return $itemDetails;
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
	private function insertItemFieldOrderedValue(int $itemId, int $fieldId, int $order, $value) {
		$qb = $this->db->getQueryBuilder();
		$qb->insert('athm_item_field_values')
			->setValue('item_id', $qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT))
			->setValue('field_id', $qb->createNamedParameter($fieldId, IQueryBuilder::PARAM_INT))
			->setValue('order', $order)
			->setValue('value', $qb->createNamedParameter($value));
		$qb->executeStatement();
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	private function insertItemFieldFirstValue(int $itemId, int $fieldId, $value) {
		$this->insertItemFieldOrderedValue($itemId, $fieldId, 0, $value);
	}
	
	private function getNextOrder(int $itemId, int $fieldId) {
		try {
			$qb = $this->db->getQueryBuilder();
			$qb->selectAlias($qb->createFunction('MAX(`order`)'), 'max_order')
				->from('athm_item_field_values')
				->where($qb->expr()
						->eq('item_id',
								$qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT)))
				->andWhere($qb->expr()
							->eq('field_id',
								$qb->createNamedParameter($fieldId, IQueryBuilder::PARAM_INT)));
			$cursor = $qb->execute();
			$row = $cursor->fetch();
			$cursor->closeCursor();
			return $row['max_order'] + 1;
		} catch (DoesNotExistException $e) {}
		return 0;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	private function insertItemFieldNextValue(int $itemId, int $fieldId, $value) {
		$nextOrder = $this->getNextOrder($itemId, $fieldId);
		$this->insertItemFieldOrderedValue($itemId, $fieldId, $nextOrder, $value);
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
	 * This function should be called within an atomic
	 */
	public function insertWithData(string $title, int $itemType, int $folder,
								   \DateTime $dateAdded, \DateTime $dateModified,
								   array $itemData, string $userId): Item {
		$item = new Item();
		$item->setTitle($title);
		$item->setItemTypeId($itemType);
		$item->setFolderId($folder);
		$item->setDateAdded($dateAdded);
		$item->setDateModified($dateModified);
		$item->setUserId($userId);

		$newItem = $this->insert($item);
		foreach ($itemData as $field=>$value) {
			$fieldId = $this->findFieldId($field);
			$this->insertItemFieldFirstValue($newItem->id, $fieldId, $value);
		}
		return $newItem;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 * This function should be called within an atomic
	 */
	public function updateWithData(int $itemId, string $title, int $itemType, int $folder,
								   \DateTime $dateModified,
								   array $itemData, string $userId) {
		$item = $this->find($itemId, $userId);
		$item->setTitle($title);
		$item->setItemTypeId($itemType);
		$item->setFolderId($folder);
		$item->setDateModified($dateModified);
		$item->setUserId($userId);

		foreach ($itemData as $field=>$value) {
			$fieldId = $this->findFieldId($field);
			$nextOrder = $this->getNextOrder($itemId, $fieldId);
			$this->insertItemFieldOrderedValue($itemId, $fieldId, $nextOrder, $value);
		}
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function insertDetailed(array $itemData, \DateTime $dateAdded,
	\DateTime $dateModified, string $userId): Item {
        return $this->atomic(function () use (&$itemData, &$dateAdded, &$dateModified, &$userId) {
			$newItemData = array();
			if (array_key_exists('url', $itemData)) {
				$itemData['url'] = $itemData['url'];
			}
			$itemTypeId = 1; # paper
			$folderId = 2; # library
			$newItem = $this->insertWithData(
				$itemData['title'], $itemTypeId, $folderId, $dateAdded, $dateModified,
				$newItemData, $userId
			);
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

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function createFromEML(array $emlData, \DateTime $dateAdded,
								  \DateTime $dateModified, string $userId): array {
		return $this->atomic(function () use (&$emlData, &$dateAdded, &$dateModified, &$userId) {
			$sourceMapper = new SourceMapper($this->db);

			$newItemIds = array();
			$source = $sourceMapper->getOrInsertByUid($emlData["alertId"], "scholarAlert");
			$emailData = array(
				'emailSubject' => $emlData["subject"],
				'alertId' => $source->getUid(),
				'searchTerm' => $emlData["searchTerm"],
				'emailReceived' => $emlData["received"]
			);
			$itemType = 1; # paper
			$folder = 1; # inbox


			foreach ($emlData["items"] as $emlItem) {
				$emailItemData = $emailData;
				$emailItemData['excerpt'] = $emlItem["excerpt"];
				$emailItemData['authors'] = $emlItem["authors"];
				$emailItemData['journal'] = $emlItem["journal"];
				$emailItemData['published'] = $emlItem["published"]; 
				$extra = json_encode($emailItemData, JSON_FORCE_OBJECT);

				$itemUrl = $emlItem["url"];
				$item = null;
				$itemIsNew = false;
				try {
					$item = $this->findByFieldValue('url', $itemUrl, $userId);
				} catch (DoesNotExistException $ie) {
					// new item
					$newItemData = array(
						'url' => $itemUrl,
						'inbox_read' => false,
						'inbox_importance' => 0,
						'inbox_needs_review' => false
					);
					
					$item = $this->insertWithData(
						$emlItem["title"], $itemType, $folder, $dateAdded,
						$dateModified, $newItemData, $userId
					);
					$itemIsNew = true;
				}
				$addSourceItem = $itemIsNew;
				$itemSourceMapper = new ItemSourceMapper($this->db);
				if (!$itemIsNew) {
					$addSourceItem = !$itemSourceMapper->itemSourceExists(
						$item->getId(), $source->getId()
					);
				}
				if ($addSourceItem) {
					$itemSource = new ItemSource();
					$itemSource->setItemId($item->getId());
					$itemSource->setSourceId($source->getId());
					$itemSource->setExtra($extra);
					$itemSourceMapper->insert($itemSource);
				}
				$newItemIds[] = $item->getId();
			}
			return $newItemIds;
        }, $this->db);
	}
}
