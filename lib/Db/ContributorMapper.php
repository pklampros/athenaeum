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
 * @template-extends QBMapper<Contributor>
 */
class ContributorMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'athm_contributors', Contributor::class);
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
								string $displayName): Array {
		$fullName = $firstName." ".$lastName;
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
			->innerJoin("ci", "athm_contributors", "co", "co.id = ci.contributor_id")
			->where($qb->expr()->andx(
				'co.last_name LIKE :ln',
				'LENGTH(co.last_name) < 3*LENGTH(:ln)'
			))
			->orWhere('ci.contributor_name_display LIKE :ln')
			->orWhere('ci.contributor_name_display LIKE :fn')
			->setParameter('ln', '%'.addcslashes($lastName, '%_').'%')
			->setParameter('dn', '%'.addcslashes($displayName, '%_').'%')
			->setParameter('fn', '%'.addcslashes($fullName, '%_').'%');

		$contributionData = [];
		$result = $qb->executeQuery();
		try {
			while ($row = $result->fetch()) {
				$contribution = [];
				$contribution['id'] = $row['id'];
				$contribution['firstName'] = $row['first_name'];
				$contribution['lastName'] = $row['last_name'];
				$contribution['displayName'] = $row['contributor_name_display'];
				$levFullName = levenshtein($fullName, $row['first_name']." ".$row['last_name']);
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
}
