<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Migration;

use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;


// This class should really not exist. Instead, whatever happens here should happen in
// postSchemaChange in the migration steps. Unfortunately this is necessary as the
// migration run schemaChange only if the installed version has not changed. So it's
// not possible to trigger a function right after installation. This is because right
// after installation the app will have the same version as right after the first
// enablement so only "schemaChange" will be created (creating the tables).

class DefaultDataRepairStep implements IRepairStep {
	
	protected $connection;
	
	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}
	
	public function getName() {
		return 'An installation step to add default data';
	}
	
	public function run(IOutput $output) {
		$query = $this->connection->getQueryBuilder();
		$query->insert('athm_item_types')
			   ->setValue('name', $query->createParameter('new_item_type'));

		$query->setParameter('new_item_type', 'author');
		$query->executeStatement();
		$query->setParameter('new_item_type', 'editor');
		$query->executeStatement();

		$query->insert('athm_fields')
			  ->setValue('name', $query->createParameter('new_field_name'))
			  ->setValue('type_hint', $query->createParameter('new_field_type_hint'));

		$query->setParameter('new_field_name', 'url');
		$query->setParameter('new_field_type_hint', 'string');
		$query->executeStatement();

		$query->insert('athm_contribution_types')
			  ->setValue('name', $query->createParameter('new_field_name'))

		$query->setParameter('new_field_name', 'author');
		$query->executeStatement();
	}
}