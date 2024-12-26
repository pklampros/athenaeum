<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

use function OCP\Log\logger;


class Version100000Date20181013124733 extends SimpleMigrationStep {

	public function __construct(
		protected IDBConnection $db,
	) {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();
		
		// throw new \Exception( "\$Migrationnn not ready" );

		$table = $schema->getTable('athm_item_sources');
		if (!$table->hasColumn('extra_item_data')) {
			$table->addColumn('extra_item_data', 'json', [
				'notnull' => false,
			]);
		}
		if (!$table->hasColumn('extra_source_data')) {
			$table->addColumn('extra_source_data', 'json', [
				'notnull' => false,
			]);
		}
		
		return $schema;
	}

	public function postSchemaChange(IOutput $output, \Closure $schemaClosure, array $options) {
		
		// throw new \Exception( "\$Migrationnn not ready" );

		$qb = $this->db->getQueryBuilder();

		$qb->selectAlias($qb->createFunction('COUNT(id)'),
		'count')->from('athm_item_sources');

		$cursor = $qb->execute();
		$row = $cursor->fetch();
		$cursor->closeCursor();
		$totalCount = $row['count'];

		$logger = logger('athenaeum');
		$logger->warning('modifying ' . $totalCount . ' records');

		$maxResults = 200;
		for($i = 0; $i < $totalCount; $i+=$maxResults) {
			
			$logger->warning('record ' . $i);

			$qb = $this->db->getQueryBuilder();

			$qb->select('id')
				->addSelect('extra')
				->from('athm_item_sources')
				->setFirstResult($i)
				->setMaxResults($maxResults);
			
			$newextras = [];
			$logger->warning('executing...');
			$result = $qb->executeQuery();
			$logger->warning('fetching..');
			try {
				$counter = 0;
				while ($row = $result->fetch()) {
					$logger->warning('record ' . ($i + $counter));
					$counter+=1;
					$extraJSON = json_decode($row['extra'], true);
					$newRow = [
					'id' => $row['id'],
					'extraItemData' => json_encode([
						'excerpt' => $extraJSON['excerpt'],
						'authors' => $extraJSON['authors'],
						'journal' => $extraJSON['journal'],
						'published' => $extraJSON['published'],
					]),
					'extraSourceData' => json_encode([
						'emailSubject' => $extraJSON['emailSubject'],
						'alertId' => $extraJSON['alertId'],
						'searchTerm' => $extraJSON['searchTerm'],
						'emailReceived' => $extraJSON['emailReceived'],
					]),
					];
					array_push($newextras, $newRow);
				}
			} finally {
				$result->closeCursor();
			}
			$logger->warning('updating..');

			$counter = 0;
			foreach ($newextras as &$row) {
				$logger->warning('updating record ' . ($i + $counter));
				$counter+=1;
				$qb = $this->db->getQueryBuilder();
				$qb->update('athm_item_sources')
					->set('extra_item_data',
						$qb->createNamedParameter($row['extraItemData']))
					->set('extra_source_data',
						$qb->createNamedParameter($row['extraSourceData']))
					->where($qb->expr()->eq('id',
						$qb->createNamedParameter($row['id'])));
				$qb->executeStatement();
			}
		}
	}

}
