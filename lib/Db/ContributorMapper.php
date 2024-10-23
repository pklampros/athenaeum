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
 * @template-extends QBMapper<Contributor>
 */
class ContributorMapper extends QBMapper {
	private IRootFolder $storage;
	private IConfig $config;
	private string $appName;

	public function __construct(IDBConnection $db,
		IRootFolder $storage, IConfig $config, string $appName) {
		parent::__construct($db, 'athm_contributors', Contributor::class);
		$this->storage = $storage;
		$this->config = $config;
		$this->appName = $appName;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): Contributor {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_contributors')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findSimilar(string $firstName, string $lastName,
		string $displayName): array {
		$firstName = strtolower($firstName);
		$lastName = strtolower($lastName);
		$displayName = strtolower($displayName);
		$fullName = $firstName . ' ' . $lastName;
		$qb = $this->db->getQueryBuilder();
		# find contributors whose last name, full name or display name is
		# similar (contain the one given). Make sure that we don't get last
		# names that are much larger than the given last name (happens when
		# given very small last names, worse as the database grows)
		$qb->select('co.id')
			->addSelect('co.first_name')
			->addSelect('co.last_name')
			->addSelect('co.last_name_is_full_name')
			->addSelect('ci.contribution_order')
			->addSelect('ci.contribution_type_id')
			->addSelect('ci.contributor_name_display')
			->from('athm_contributions', 'ci')
			->innerJoin('ci', 'athm_contributors', 'co', 'co.id = ci.contributor_id')
			->where($qb->expr()->andx(
				'LOWER(co.last_name) LIKE :ln',
				'LENGTH(co.last_name) < 3*LENGTH(:ln)'
			))
			->orWhere('LOWER(ci.contributor_name_display) LIKE :dn')
			->orWhere('LOWER(ci.contributor_name_display) LIKE :fn')
			->setParameter('ln', '%' . addcslashes($lastName, '%_') . '%')
			->setParameter('dn', '%' . addcslashes($displayName, '%_') . '%')
			->setParameter('fn', '%' . addcslashes($fullName, '%_') . '%');

		$contributionData = [];
		$result = $qb->executeQuery();
		try {
			while ($row = $result->fetch()) {
				$contribution = [];
				$contribution['id'] = $row['id'];
				$contribution['firstName'] = $row['first_name'];
				$contribution['lastName'] = $row['last_name'];
				$contribution['displayName'] = $row['contributor_name_display'];
				$levFullName = levenshtein($fullName, $row['first_name'] . ' ' . $row['last_name']);
				$levDisplayName = levenshtein($displayName, $row['contributor_name_display']);
				$contribution['lev_similarity'] = max($levFullName, $levDisplayName);
				array_push($contributionData, $contribution);
			}
		} finally {
			$result->closeCursor();
		}
		return $contributionData;
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function freeSearch(string $term): array {
		$nameParts = explode(' ', $term);
		$lastName = end($nameParts);
		$firstName = '';
		if (sizeof($nameParts) > 1) {
			$firstName = implode(' ', array_slice($nameParts, 0, -1));
		}
		return $this->findSimilar($firstName, $lastName, $term);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByFirstLastName(string $firstName, string $lastName): Contributor {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_contributors')
			->where($qb->expr()
				->eq('first_name', $qb->createNamedParameter($firstName)))
			->andWhere($qb->expr()
				->eq('last_name', $qb->createNamedParameter($lastName)));
		return $this->findEntity($qb);
	}

	/**
	 * @return array
	 */
	public function findAll(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('athm_contributors');
		return $this->findEntities($qb);
	}

	public function insertContributor(Contributor $entity): Contributor {
		$result = $this->insert($entity);
		$this->saveToJSONOnModify($entity->getId(), $entity->getUserId());
		return $result;
	}

	public function updateContributor(Contributor $entity): Contributor {
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
		$contributor = $this->find($id, $userId);
		
		$fsh = new FilesystemHandler($this->storage);
		$contributorDatafolder = $fsh->getContributorsDataFolder($userId, $id);

		$fileName = 'contributor.json';

		try {
			$contributorDatafolder->get($fileName);
		} catch (\OCP\Files\NotFoundException $e) {
			// does not exist, continue
		}
		
		$dbid = $this->config->getUserValue($userId, $this->appName, 'dbid');

		if ($dbid == '') {
			throw new \Exception('dbid not found!');
		}

		$contributorData = [];
		$contributorData['details'] = $contributor;
		$contributorData['dbid'] = $dbid;
		$contributorData['written'] = new \DateTime;

		$contributorDatafolder->newFile($fileName, json_encode($contributorData));
	}
}
