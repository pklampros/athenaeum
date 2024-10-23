<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\Files\IRootFolder;
use OCP\IConfig;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<Source>
 */
class SourceMapper extends QBMapper {
	private IRootFolder $storage;
	private IConfig $config;
	private string $appName;
	
	public function __construct(IDBConnection $db,
		IRootFolder $storage, IConfig $config, string $appName) {
		parent::__construct($db, 'athm_sources', Source::class);
		$this->storage = $storage;
		$this->config = $config;
		$this->appName = $appName;
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
			$currentDate = new \DateTime;
			$entity->setDateAdded($currentDate);
			$entity->setDateModified($currentDate);
			$source = $this->insertSource($entity);
		}
		return $source;
	}

	public function insertSource(Source $entity): Source {
		$result = $this->insert($entity);
		$this->saveToJSONOnModify($entity->getId(), $entity->getUserId());
		return $result;
	}

	public function updateSource(Source $entity): Source {
		$result = $this->update($entity);
		$this->saveToJSONOnModify($entity->getId(), $entity->getUserId());
		return $result;
	}

	private function saveToJSONOnModify(int $id, string $userId) {
		$value = $this->config->getUserValue(
			$userId,
			'athenaeum',
			'json_export_frequency'
		);
		
		if ($value == 'onmodify') {
			try {
				return $this->saveToJSON($id, $userId);
			} catch (Exception $e) {
				$this->handleException($e);
			}
		}
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	private function saveToJSON(int $id, string $userId) {

		// try to find the contributor, excepts if none/many found
		$source = $this->find($id, $userId);
		
		$fsh = new FilesystemHandler($this->storage);
		$sourcesDatafolder = $fsh->getSourcesDataFolder($userId, $id);

		$fileName = 'source.json';

		try {
			$sourcesDatafolder->get($fileName);
		} catch (\OCP\Files\NotFoundException $e) {
			// does not exist, continue
		}
		
		$dbid = $this->config->getUserValue($userId, $this->appName, 'dbid');

		if ($dbid == '') {
			throw new \Exception('dbid not found!');
		}

		$sourceData = [];
		$sourceData['details'] = $source;
		$sourceData['dbid'] = $dbid;
		$sourceData['written'] = new \DateTime;

		$sourcesDatafolder->newFile($fileName, json_encode($sourceData));
	}
}
