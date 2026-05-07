<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use OCA\Athenaeum\Error\UrlFetchError;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Db\TTransactional;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\Files\IRootFolder;
use OCP\IConfig;
use OCP\IDBConnection;
use Shanept\MimeReader;

/**
 * @template-extends QBMapper<Item>
 */
class ItemMapper extends QBMapper {
	use TTransactional;
	private IRootFolder $storage;
	private IConfig $config;
	private string $appName;

	public function __construct(IDBConnection $db,
		IRootFolder $storage, IConfig $config, string $appName) {
		parent::__construct($db, 'athm_items', Item::class);
		$this->storage = $storage;
		$this->config = $config;
		$this->appName = $appName;
	}

	public function insertItem(Item $entity): Item {
		$result = $this->insert($entity);
		$this->saveToJSONOnModify($entity->getId(), $entity->getUserId());
		return $result;
	}

	public function updateItem(Item $entity): Item {
		$result = $this->update($entity);
		$this->saveToJSONOnModify($entity->getId(), $entity->getUserId());
		return $result;
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
			->where($qb->expr()->eq('id',
				$qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id',
				$qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)));
		return $this->findEntity($qb);
	}

	private function getContributions($id) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('co.id')
			->addSelect('co.first_name')
			->addSelect('co.last_name')
			->addSelect('co.last_name_is_full_name')
			->addSelect('ci.contribution_order')
			->addSelect('ci.contribution_type_id')
			->addSelect('ci.contributor_name_display')
			->from('athm_contributions', 'ci')
			->innerJoin('ci', 'athm_contributors', 'co', 'co.id = ci.contributor_id')
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
		return $contributions;
	}

	private function getFieldData($id) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('ifv.order')
			->addSelect('ifv.value')
			->addSelect('f.name')
			->addSelect('f.type_hint')
			->from('athm_fields', 'f')
			->innerJoin('f', 'athm_item_field_values', 'ifv', 'f.id = ifv.field_id')
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
		return $fieldData;
	}

	public function getAttachments($itemId, $userId): array {
		$itemAttachmentMapper = new ItemAttachmentMapper($this->db, $this->storage);
		
		$itemFileAttachments = [];

		$itemAttachments = $itemAttachmentMapper->findAllByItem(
			$itemId, $userId);
		foreach ($itemAttachments as $itemAttachment) {
			array_push($itemFileAttachments,
				$this->wrapInItemFileAttachment($itemAttachment));
		}
		return $itemFileAttachments;
	}

	public function removeAttachment($attachmentId, $userId): bool {
		$itemAttachmentMapper = new ItemAttachmentMapper($this->db, $this->storage);

		return $itemAttachmentMapper->removeAttachment($attachmentId, $userId);
	}

	private function getSourceInfo($id) {

		$qb = $this->db->getQueryBuilder();
		$qb->select('its.extra_item_data')
			->addSelect('its.extra_source_data')
			->addSelect('s.importance')
			->addSelect('s.source_type')
			->from('athm_item_sources', 'its')
			->innerJoin('its', 'athm_sources', 's', 's.id = its.source_id')
			->where($qb->expr()->eq('its.item_id',
				$qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		
		$result = $qb->executeQuery();
		$sourceInfo = [];
		try {
			while ($fieldData = $result->fetch()) {
				$fieldData['extra_item_data'] = json_decode($fieldData['extra_item_data'], true);
				$fieldData['extra_source_data'] = json_decode($fieldData['extra_source_data'], true);
				$sourceInfo[] = $fieldData;
				// $sourceData = json_decode($fieldData["extra"], true);
				// unset($sourceData['sourceId']);
				// $inboxItem->setAuthors($sourceData['authors']);
				// $inboxItem->setJournal($sourceData['journal']);
				// $inboxItem->setPublished($sourceData['published']);
				// $extra[] = array(
				// 	'importance' => $fieldData['importance'],
				// 	'type' => $fieldData['source_type'],
				// 	'extra' => array(
				// 		'searchTerm' => $sourceData['searchTerm'],
				// 		'excerpt' => $sourceData['excerpt']
				// 	)
				// );
			}
		} finally {
			$result->closeCursor();
		}
		return $sourceInfo;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function getWithDetails(int $id, string $userId): ItemDetails {
		$itemDetails = new ItemDetails();
		$itemDetails->setItem($this->find($id, $userId));
		$itemDetails->setContributions($this->getContributions($id));
		$itemDetails->setFieldData($this->getFieldData($id));
		$itemDetails->setAttachments($this->getAttachments($id, $userId));
		$itemDetails->setSourceInfo($this->getSourceInfo($id));

		return $itemDetails;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function getSummary(int $id, string $userId): ItemDetails {
		$itemDetails = new ItemDetails();
		$itemDetails->setItem($this->find($id, $userId));
		// Contributions only exist if the item has been added to the library
		$itemDetails->setContributions($this->getContributions($id));
		// While the item is in the inbox, it only contains unstructured data
		// as that has been provided in the source
		$itemDetails->setSourceInfo($this->getSourceInfo($id));

		return $itemDetails;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function getInboxItemDetails(int $id, string $userId): InboxItemDetails {
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
			->innerJoin('f', 'athm_item_field_values', 'ifv', 'f.id = ifv.field_id')
			->where($qb->expr()->eq('ifv.item_id',
				$qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		
		$result = $qb->executeQuery();
		try {
			while ($fieldData = $result->fetch()) {
				$field = $fieldData['name'];
				$value = $fieldData['value'];
				if ($field == 'url') {
					$inboxItem->setUrl($value);
				} elseif ($field == 'inbox_read') {
					$inboxItem->setRead($value);
				} elseif ($field == 'inbox_importance') {
					$inboxItem->setImportance($value);
				} elseif ($field == 'inbox_needs_review') {
					$inboxItem->setNeedsReview($value);
				} elseif ($field == 'inbox_source_data') {
					$sourceData = json_decode($value, true);
					unset($sourceData['sourceId']);
					$itemDetails->setSourceData($sourceData);
				}
			}
		} finally {
			$result->closeCursor();
		}

		
		$itemDetails->setSourceInfo($this->getSourceInfo($id));

		return $itemDetails;
	}

	public function decideLater(int $id, string $userId) {
		$this->changeFolder($id, $this->findFolderId('inbox/decide_later', $userId));
	}
	
	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByFieldValue(string $fieldName, string $fieldValue,
		string $userId): Item {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('i.*')
			->from('athm_items', 'i')
			->where($qb->expr()->eq('i.user_id',
				$qb->createNamedParameter($userId)))
			->innerJoin('i', 'athm_item_field_values', 'ifv', 'i.id = ifv.item_id')
			->innerJoin('i', 'athm_fields', 'f', 'f.id = ifv.field_id')
			->where($qb->expr()->eq('f.name',
				$qb->createNamedParameter($fieldName)))
			->andWhere($qb->expr()->eq('ifv.value',
				$qb->createNamedParameter($fieldValue)));
		return $this->findEntity($qb);
	}

	/**
	 * @param string $userId
	 * @return array
	 */
	public function findAll(
		string $userId,
		int $folderId,
		int $limit,
		int $offset,
		string $orderBy,
		string $search,
		?bool $showAll = false,
	): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();

		$titleonly = false;

		if (isset($search) && $search != '') {
			$qb->select('it.title')
				->from('athm_items', 'it')
				->where($qb->expr()->eq('it.user_id',
					$qb->createNamedParameter($userId)))
				->andWhere($qb->expr()->eq('it.folder_id',
					$qb->createNamedParameter($folderId)));
			if ($titleonly) {
				$qb->addSelect('it.title')
					->having($qb->expr()->iLike(
						'it.title',
						$qb->createNamedParameter('%' . $search . '%'),
						IQueryBuilder::PARAM_STR));
			} else {
				$or = $qb->expr()->orx(
					$qb->expr()->iLike(
						'it.title',
						$qb->createNamedParameter('%' . $search . '%'),
						IQueryBuilder::PARAM_STR),
					$qb->expr()->iLike(
						'source_extra',
						$qb->createNamedParameter('%' . $search . '%'),
						IQueryBuilder::PARAM_STR),
				);
	
				$qb->selectAlias($qb->func()->groupConcat('its.extra_item_data'),
					'source_extra')
					->leftJoin('it', 'athm_item_sources', 'its', 'it.id = its.item_id')
					->groupBy('it.id')
					->having($or);
			}
			$cursor = $qb->executeQuery();
			$totalCount = count($cursor->fetchAll());
			$cursor->closeCursor();
		} else {
			$qb->selectAlias($qb->createFunction('COUNT(it.id)'),
				'count')
				->from('athm_items', 'it')
				->where($qb->expr()->eq('it.user_id',
					$qb->createNamedParameter($userId)))
				->andWhere($qb->expr()->eq('it.folder_id',
					$qb->createNamedParameter($folderId)));
			$cursor = $qb->executeQuery();
			$row = $cursor->fetch();
			$cursor->closeCursor();
			$totalCount = $row['count'];
		}


		$qb = $this->db->getQueryBuilder();
		$qb->selectAlias($qb->createFunction('SUM(`s`.`importance`)'),
			'source_importance')
			->addSelect('it.*')
			->from('athm_items', 'it')
			->where($qb->expr()->eq('it.user_id',
				$qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('it.folder_id',
				$qb->createNamedParameter($folderId)))
			->leftJoin('it', 'athm_item_sources', 'its', 'it.id = its.item_id')
			->leftJoin('its', 'athm_sources', 's', 's.id = its.source_id')
			->groupBy('it.id')
			->setFirstResult($offset)
			->setMaxResults($limit);

		$firstOrderValue = true;
		$keyColumn = [
			'da' => 'it.date_added',
			'dm' => 'it.date_modified',
			'si' => 'source_importance',
		];
		
		foreach (explode(',', $orderBy) as &$orderByValue) {
			$direction = 'asc';
			if (str_starts_with($orderByValue, '-')) {
				$direction = 'desc';
				$orderByValue = substr($orderByValue, 1);
			}

			$colName = $keyColumn[$orderByValue];
			if (isset($colName)) {
				if ($firstOrderValue) {
					$qb->orderBy($colName, $direction);
					$firstOrderValue = false;
				} else {
					$qb->addOrderBy($colName, $direction);
				}
			}
		}

		// Always order by id last to maintain the ultimate order of
		// items across page refreshes
		$qb->addOrderBy('it.id', 'DESC');

		$includeAttachments = true;

		if ($includeAttachments) {
			$sqb = $this->db->getQueryBuilder();
			$sqb->select('item_id')
				->selectAlias($qb->createFunction('COUNT(id)'), 'count')
				->from('athm_item_attchm')
				->groupBy('item_id');
				
			// We can't use athm_item_attchm directly because there might be more
			// than one attachments and that would duplicate the rows, causing the
			// source importance to be counted as many times
			$qb->selectAlias('a.count', 'num_attachments')
				->leftJoin('it', $qb->createFunction('(' . $sqb->getSQL() . ')'),
					'a', 'it.id = a.item_id');
		}

		if (isset($search) && $search != '') {

			if ($titleonly) {
				$qb->addSelect('it.title')
					->having($qb->expr()->iLike(
						'it.title',
						$qb->createNamedParameter('%' . $search . '%'),
						IQueryBuilder::PARAM_STR));
			} else {
				$or = $qb->expr()->orx(
					$qb->expr()->iLike(
						'it.title',
						$qb->createNamedParameter('%' . $search . '%'),
						IQueryBuilder::PARAM_STR),
					$qb->expr()->iLike(
						'source_extra',
						$qb->createNamedParameter('%' . $search . '%'),
						IQueryBuilder::PARAM_STR),
				);
	
				$qb->selectAlias($qb->func()->groupConcat('its.extra_item_data'),
					'source_extra')
					->having($or);
			}
		}

		return [
			'items' => $this->findEntities($qb),
			'offset' => $offset,
			'totalCount' => $totalCount
		];
	}

	public function getWordFrequency(int $folderId, string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->selectAlias('it.title', 'title')
			->selectAlias($qb->func()->groupConcat('its.extra_item_data'),
				'source_extra')
			->from('athm_items', 'it')
			->where($qb->expr()->eq('it.user_id',
				$qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('it.folder_id',
				$qb->createNamedParameter($folderId)))
			->leftJoin('it', 'athm_item_sources', 'its', 'it.id = its.item_id')
			->leftJoin('its', 'athm_sources', 's', 's.id = its.source_id')
			->groupBy('it.id');

		$allval = [];
		$result = $qb->executeQuery();
		try {
			while ($row = $result->fetch()) {
				$tval = array_count_values(str_word_count(strtolower($row['title']), 1));
				foreach ($tval as $key => $value) {
					$allval[$key] += $value;
				}
				$sval = array_count_values(str_word_count(strtolower($row['source_extra']), 1));
				foreach ($sval as $key => $value) {
					$allval[$key] += $value;
				}
			}
		} finally {
			$result->closeCursor();
		}
		return $allval;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 *                               This asssumes that the tables have an "id" field
	 */
	private function getIdFromColumnValue(string $table, string $colName,
		string $value): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select('id')
			->from($table)
			->where($qb->expr()->eq($colName, $qb->createNamedParameter($value)));
		
		$result = $qb->executeQuery();
		$resultId = 0; # functions as null
		try {
			$sourceInfo = [];
			while ($fieldData = $result->fetch()) {
				if ($resultId != 0) {
					throw new MultipleObjectsReturnedException();
				}
				$resultId = $fieldData['id'];
			}
		} finally {
			$result->closeCursor();
		}
		if ($resultId == 0) {
			throw new DoesNotExistException();
		}
		return $resultId;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findFieldId(string $fieldName): int {
		return $this->getIdFromColumnValue('athm_fields', 'name', $fieldName);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findItemTypeId(string $itemTypeName): int {
		return $this->getIdFromColumnValue('athm_item_types', 'name', $itemTypeName);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findFolderId(string $folderPath): int {
		return $this->getIdFromColumnValue('athm_folders', 'path', $folderPath);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	private function insertItemFieldOrderedValue(int $itemId, int $fieldId,
		int $order, $value) {
		$qb = $this->db->getQueryBuilder();
		$qb->insert('athm_item_field_values')
			->setValue('item_id',
				$qb->createNamedParameter($itemId, IQueryBuilder::PARAM_INT))
			->setValue('field_id',
				$qb->createNamedParameter($fieldId, IQueryBuilder::PARAM_INT))
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
						$qb->createNamedParameter($itemId,
							IQueryBuilder::PARAM_INT)))
				->andWhere($qb->expr()
					->eq('field_id',
						$qb->createNamedParameter($fieldId,
							IQueryBuilder::PARAM_INT)));
			$cursor = $qb->executeQuery();
			$row = $cursor->fetch();
			$cursor->closeCursor();
			return $row['max_order'] + 1;
		} catch (DoesNotExistException $e) {
		}
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
	 *                               This function should be called within an atomic
	 */
	public function insertWithData(string $title, int $itemTypeId, int $folderId,
		\DateTime $dateAdded, \DateTime $dateModified,
		array $itemData, string $userId): Item {
		$item = new Item();
		$item->setTitle($title);
		$item->setItemTypeId($itemTypeId);
		$item->setFolderId($folderId);
		$item->setDateAdded($dateAdded);
		$item->setDateModified($dateModified);
		$item->setUserId($userId);

		$newItem = $this->insert($item);
		foreach ($itemData as $field => $value) {
			$fieldId = $this->findFieldId($field);
			$this->insertItemFieldFirstValue($newItem->id, $fieldId, $value);
		}
		$this->saveToJSONOnModify($newItem->id, $userId);
		return $newItem;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function changeFolder(int $itemId, int $folderId, string $userId)
	: Item {
		$item = $this->find($itemId, $userId);
		$item->setFolderId($folderId);
		$item->setDateModified(new \DateTime);

		$this->update($item);

		$this->saveToJSONOnModify($itemId, $userId);
		
		return $item;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 *
	 * This function should be called within an atomic
	 */
	public function updateWithData(int $itemId, string $title, int $itemTypeId,
		\DateTime $dateModified,
		array $itemData, string $userId): Item {
		$item = $this->find($itemId, $userId);
		$item->setTitle($title);
		$item->setItemTypeId($itemTypeId);
		$item->setDateModified($dateModified);
		$item->setUserId($userId);

		$this->update($item);
		
		foreach ($itemData as $field => $value) {
			$fieldId = $this->findFieldId($field);
			$nextOrder = $this->getNextOrder($itemId, $fieldId);
			$this->insertItemFieldOrderedValue($itemId, $fieldId, $nextOrder,
				$value);
		}
		$this->saveToJSONOnModify($itemId, $userId);
		return $item;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function inboxToDecideLater(int $id, string $userId): Item {
		$folderId = $this->findFolderId('inbox/decide_later');
		$this->changeFolder($id, $folderId);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function scholarToFull(int $id, array $itemData, \DateTime $dateAdded,
		\DateTime $dateModified, string $userId): Item {
		return $this->atomic(function () use (&$id, &$itemData, &$dateAdded,
			&$dateModified, &$userId) {
			$newItemData = [];
			if (array_key_exists('url', $itemData)) {
				$itemData['url'] = $itemData['url'];
			}
			$itemTypeId = $this->findItemTypeId('paper');
			if (array_key_exists('authorList', $itemData)) {
				$contributorMapper = new ContributorMapper($this->db, $this->storage,
					$this->config, $this->appName);
				$contributionMapper = new ContributionMapper($this->db);
				foreach ($itemData['authorList'] as $index => $author) {
					$contributor = new Contributor();
					if ($author['onlyLastName']) {
						$contributor->setLastName($author['name']);
						$contributor->setLastNameIsFullName($author['onlyLastName']);
					} else {
						$contributor->setFirstName($author['firstName']);
						$contributor->setLastName($author['name']);
						$contributor->setLastNameIsFullName($author['onlyLastName']);
					}
					$contributor->setUserId($userId);
					$currentDate = new \DateTime;
					$contributor->setDateAdded($currentDate);
					$contributor->setDateModified($currentDate);
					$newContributor = $contributorMapper->insertContributor(
						$contributor
					);
					
					$contribution = new Contribution();
					$contribution->setItemId($id);
					$contribution->setContributorId($newContributor->id);
					$contribution->setContributorNameDisplay($author['displayName']);
					$contribution->setContributionTypeId(1);
					$contribution->setContributionOrder($index + 1);
					$newContribution = $contributionMapper->insert($contribution);
				}
			}
			return $this->updateWithData(
				$id, $itemData['title'], $itemTypeId,
				$dateModified, $newItemData, $userId
			);
			;
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
				->setValue('item_id',
					$qb->createNamedParameter($newItem->id,
						IQueryBuilder::PARAM_INT))
				->setValue('field_id',
					$qb->createNamedParameter($fieldID,
						IQueryBuilder::PARAM_INT))
				->setValue('order', 0)
				->setValue('value', $qb->createNamedParameter($url));
			$qb->executeStatement();
			$this->saveToJSONOnModify($newItem->id, $newItem->userId);
			return $newItem;
		}, $this->db);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function createFromEML(array $emlData, \DateTime $dateAdded,
		\DateTime $dateModified, string $userId): array {
		return $this->atomic(function () use (&$emlData, &$dateAdded,
			&$dateModified, &$userId) {
			$sourceMapper = new SourceMapper($this->db, $this->storage,
				$this->config, $this->appName);

			$itemResultData = [];
			$trimmedTerm = $emlData['searchTerm'];
			if (strlen($trimmedTerm) > 7) {
				$trimmedTerm = substr($trimmedTerm, 0, 6) . '...';
			}
			$source = $sourceMapper->getOrInsertByUid(
				$emlData['alertId'], 'scholarAlert', 0,
				'Scholar alert (' . $trimmedTerm . ')',
				'Scholar alert for the search term: ' . $emlData['searchTerm'],
				$userId
			);
			$extraSourceData = json_encode([
				'emailSubject' => $emlData['subject'],
				'alertId' => $source->getUid(),
				'searchTerm' => $emlData['searchTerm'],
				'emailReceived' => $emlData['received']
			], JSON_FORCE_OBJECT);
			$itemTypeId = $this->findItemTypeId('paper');
			$defaultFolder = 'inbox';
			$folderId = $this->findFolderId($defaultFolder);


			foreach ($emlData['items'] as $emlItem) {
				$emailItemData = [];
				$emailItemData['excerpt'] = $emlItem['excerpt'];
				$emailItemData['authors'] = $emlItem['authors'];
				$emailItemData['journal'] = $emlItem['journal'];
				$emailItemData['published'] = $emlItem['published'];
				$extraItemData = json_encode($emailItemData, JSON_FORCE_OBJECT);

				$itemUrl = $emlItem['url'];
				$item = null;
				$itemIsNew = false;
				$itemFolderPath = $defaultFolder;
				try {
					$item = $this->findByFieldValue('url', $itemUrl, $userId);
					$folderMapper = new FolderMapper($this->db);
					$folder = $folderMapper->find($item->getFolderId(), $userId);
					$itemFolderPath = $folder->getPath();
				} catch (DoesNotExistException $ie) {
					// new item
					$newItemData = [
						'url' => $itemUrl,
						'inbox_read' => false,
						'inbox_importance' => 0,
						'inbox_needs_review' => false
					];
					
					$item = $this->insertWithData(
						$emlItem['title'], $itemTypeId, $folderId, $dateAdded,
						$dateModified, $newItemData, $userId
					);
					$itemIsNew = true;
				}
				$itemSourceIsNew = $itemIsNew;
				$itemSourceMapper = new ItemSourceMapper($this->db);
				if (!$itemIsNew) {
					$itemSourceIsNew = !$itemSourceMapper->itemSourceExists(
						$item->getId(), $source->getId(), $userId
					);
				}
				if ($itemSourceIsNew) {
					$itemSource = new ItemSource();
					$itemSource->setItemId($item->getId());
					$itemSource->setSourceId($source->getId());
					$itemSource->setExtraItemData($extraItemData);
					$itemSource->setExtraSourceData($extraSourceData);
					$itemSource->setUserId($userId);
					$itemSourceMapper->insert($itemSource);
				}
				$itemResultData[] = [
					'id' => $item->getId(),
					'folderPath' => $itemFolderPath,
					'title' => $item->getTitle(),
					'item_new' => $itemIsNew,
					'item_source_new' => $itemSourceIsNew
				];
			}
			return $itemResultData;
		}, $this->db);
	}

	private function saveToJSONOnModify(int $itemId, string $userId) {
		$value = $this->config->getUserValue(
			$userId,
			'athenaeum',
			'json_export_frequency'
		);
		
		if ($value == 'onmodify') {
			try {
				return $this->saveToJSON($itemId, $userId);
			} catch (Exception $e) {
				$this->handleException($e);
			}
		}
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	private function saveToJSON(int $itemId, string $userId) {

		// try to find the item, excepts if none/many found
		$item = $this->find($itemId, $userId);
		
		$fsh = new FilesystemHandler($this->storage);
		$itemDatafolder = $fsh->getItemDataFolder($userId, $itemId);

		$fileName = 'item.json';

		try {
			$itemDatafolder->get($fileName);
		} catch (\OCP\Files\NotFoundException $e) {
			// does not exist, continue
		}
		$itemDetails = $this->getWithDetails($itemId, $userId);
		
		$dbid = $this->config->getUserValue($userId, $this->appName, 'dbid');

		if ($dbid == '') {
			throw new \Exception('dbid not found!');
		}

		$itemData = [];
		$itemData['details'] = $itemDetails;
		$itemData['dbid'] = $dbid;
		$itemData['written'] = new \DateTime;

		$itemDatafolder->newFile($fileName, json_encode($itemData));
	}

	// https://stackoverflow.com/a/47618721
	public function getAbsoluteURL($to, $from = null) {
		$arTarget = parse_url($to);
		$arSource = parse_url($from);
		$targetPath = isset($arTarget['path']) ? $arTarget['path'] : '';
	
		if (isset($arTarget['host'])) {
			if (!isset($arTarget['scheme'])) {
				$proto = isset($arSource['scheme'])
				? "{$arSource['scheme']}://"
				: '//';
			} else {
				$proto = "{$arTarget['scheme']}://";
			}
			$baseUrl = "{$proto}{$arTarget['host']}" . (isset($arTarget['port'])
				? ":{$arTarget['port']}"
				: '');
		} else {
			if (isset($arSource['host'])) {
				$proto = isset($arSource['scheme'])
					? "{$arSource['scheme']}://"
					: '//';
				$baseUrl = "{$proto}{$arSource['host']}" . (isset($arSource['port'])
					? ":{$arSource['port']}"
					: '');
			} else {
				$baseUrl = '';
			}
			$arPath = [];
	
			if ((empty($targetPath) || $targetPath[0] !== '/')
				&& !empty($arSource['path'])) {
				$arTargetPath = explode('/', $targetPath);
				if (empty($arSource['path'])) {
					$arPath = [];
				} else {
					$arPath = explode('/', $arSource['path']);
					array_pop($arPath);
				}
				$len = count($arPath);
				foreach ($arTargetPath as $idx => $component) {
					if ($component === '..') {
						if ($len > 1) {
							$len--;
							array_pop($arPath);
						}
					} elseif ($component !== '.') {
						$len++;
						array_push($arPath, $component);
					}
				}
				$targetPath = implode('/', $arPath);
			}
		}
	
		return $baseUrl . $targetPath;
	}

	// https://stackoverflow.com/a/37588381
	public function getUrlContentsAndFinalUrl(&$url, &$response_header,
		&$response_code) {
		$maxDepth = 5;
		$depth = 1;
		$header = null;
		do {
			if ($header == null) {
				$context = stream_context_create(
					[
						'http' => [
							'follow_location' => false,
						],
					]
				);
			} else {
				$context = stream_context_create(
					[
						'http' => [
							'follow_location' => false,
							'header' => $header,
						],
					]
				);
			}

			$result = @file_get_contents($url, false, $context);

			$pattern = "/^Location:\s*(.*)$/i";
			$location_headers = preg_grep($pattern, $http_response_header);
			$response_header = $http_response_header;

			if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#",
				$http_response_header[0], $out)) {
				$response_code = intval($out[1]);
			}

			if ($response_code == 403 && $header == null) {
				// Forbidden, try to set an agent
				$header = "Accept-language: en\r\n" .
				   'User-Agent: Mozilla/5.0 (X11; Linux x86_64) ' .
				   'AppleWebKit/537.36 (KHTML, like Gecko) ' .
				   "Chrome/130.0.0.0 Safari/537.36\r\n";
				$repeat = true;
			} elseif (!empty($location_headers) &&
				preg_match($pattern, array_values($location_headers)[0],
					$matches)) {
				$url = $this->getAbsoluteURL($matches[1], $url);
				$repeat = $depth < $maxDepth;
				$depth = $depth + 1;
			} else {
				$repeat = false;
			}
		} while ($repeat);

		return $result;
	}

	/**
	 * @throws UrlFetchError
	 */
	public function attachFromUrl(string $userId, int $itemId, string $url)
	: ItemFileAttachment {
		$response_header = [];
		$response_code = 0;
		$fileData = $this->getUrlContentsAndFinalUrl($url, $response_header,
			$response_code);
		
		if ($response_code != 200) {
			throw new UrlFetchError(
				'Error fetching file (error: ' . $response_code . ')'
			);
		}
		$fileName = basename(strtok(strtok($url, '?'), '#'));

		$tempFile = tempnam(sys_get_temp_dir(), 'TMP_');
		file_put_contents($tempFile, $fileData);

		$fileMime = 'application/octet-stream';
		$headers = implode("\n", $response_header);
		if (preg_match_all("/^content-type\s*:\s*(.*)$/mi", $headers, $matches)) {
			$fileMime = end($matches[1]);
		}
		if ($fileMime == 'application/octet-stream') {
			$mime = new MimeReader($tempFile);
			$fileMime = $mime->getType();
		}

		$pathInfo = pathinfo($fileName);
		if (!array_key_exists('extension', $pathInfo) &&
			str_starts_with($fileMime, 'application/pdf')) {
			$fileName = $fileName . '.' . 'pdf';
		}

		return $this->attachFile($itemId, $fileName, $fileMime,
			strlen($fileData), $fileData, $userId);
	
	}

	private function wrapInItemFileAttachment(ItemAttachment $itemAttachment)
	: ItemFileAttachment {
		$itemFileAttachment = new ItemFileAttachment();
		$itemFileAttachment->setItemAttachment($itemAttachment);
		$itemFileAttachment->setDownloadPath(
			'/remote.php/dav/files/' . $itemAttachment->getUserId() .
			'/Athenaeum' . $itemAttachment->getPath());

		$itemFileName = basename($itemAttachment->getPath());
		$itemDir = dirname($itemAttachment->getPath());

		$fsh = new FilesystemHandler($this->storage);
		$itemAttachmentsfolder = $fsh->getItemAttachmentsFolder(
			$itemAttachment->getUserId(), $itemAttachment->getItemId());
		
		$fileId = $itemAttachmentsfolder->get($itemFileName)->getId();

		$itemFileAttachment->setOpenPath('/f/' . $fileId);
		return $itemFileAttachment;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function attachFile(int $itemId, string $fileName, string $fileMime,
		int $fileSize, $fileData, string $userId): ItemFileAttachment {
		return $this->atomic(function () use (&$itemId, &$fileName, &$fileMime,
			&$fileSize, &$fileData, &$userId) {

			// try to find the item, excepts if none/many found
			$this->find($itemId, $userId);

			$itemAttachmentMapper = new ItemAttachmentMapper($this->db,
				$this->storage);

			// increment dupeCount (up to 1000) until we find
			// a filename that doesn't exist
			$givenFileName = $fileName;
			for ($dupeCount = 1; $dupeCount <= 1000; $dupeCount += 1) {
				if (!$itemAttachmentMapper->pathExists($itemId, $givenFileName)) {
					// path does not exist in the database, try to create the file
					try {
						$newFilePath = $itemAttachmentMapper->createAttachmentFile(
							$itemId, $givenFileName, $fileData, $userId);
						// file does not exist, create and continue
						break;
					} catch (FileExistsException $e) {
						// file already exists, keep trying names
					}
				}
				$pathInfo = pathinfo($fileName);

				if (array_key_exists('extension', $pathInfo)) {
					$givenFileName = $pathInfo['filename'] . '_' . $dupeCount .
									 '.' . $pathInfo['extension'];
				} else {
					$givenFileName = $pathInfo['filename'] . '_' . $dupeCount;
				}
			}

			$itemAttachment = new ItemAttachment();
			$itemAttachment->setItemId($itemId);
			$itemAttachment->setPath($newFilePath);
			$itemAttachment->setMimeType($fileMime);
			$itemAttachment->setUserId($userId);

			try {
				$newItemAttachment = $itemAttachmentMapper->insert($itemAttachment);
			} catch (Exception $e) {
				// failed to add file to the database, delete from filesystem
				$itemAttachment->delete();
				throw new AttachmentNotAddedError();
			}

			$this->saveToJSONOnModify($itemId, $userId);
			
			return $this->wrapInItemFileAttachment($newItemAttachment);
		}, $this->db);
		return null;
	}
}
