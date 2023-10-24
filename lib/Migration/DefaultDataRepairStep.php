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
	private string $userId;
	
	public function __construct(IDBConnection $connection,
								string $userId) {
		$this->connection = $connection;
		$this->userId = $userId;
	}
	
	public function getName() {
		return 'An installation step to add default data';
	}
	
	public function run(IOutput $output) {

		// add default config fields
		$query = $this->connection->getQueryBuilder();
		$query->insert('athm_cfg_fields')
			  ->setValue('name', $query->createParameter('new_field_name'))
			  ->setValue('type_hint', $query->createParameter('new_field_type_hint'));

	    $dbid_field = 'dbid';
		$query->setParameter('new_field_name', $dbid_field);
		$query->setParameter('new_field_type_hint', 'string');
		$query->executeStatement();

		// get the newly added field's id
		$query = $this->connection->getQueryBuilder();
		$query->select('id')
			  ->from('athm_cfg_fields')
			  ->where($query->expr()->eq('name', $query->createNamedParameter($dbid_field)));
		$cursor = $query->execute();
		$row = $cursor->fetch();
		$cursor->closeCursor();
		$dbid_field_id = $row['id'];

		// add first config fields values
		$query = $this->connection->getQueryBuilder();
		$query->insert('athm_cfg_field_values')
			  ->setValue('user_id', $query->createParameter('new_user_id'))
			  ->setValue('field_id', $query->createParameter('new_field_id'))
			  ->setValue('value', $query->createParameter('new_value'));

		$query->setParameter('new_user_id', $this->userId);
		$query->setParameter('new_field_id', $dbid_field_id);
		$query->setParameter('new_value', uniqid());
		$query->executeStatement();

		// add default item types
		$query = $this->connection->getQueryBuilder();
		$query->insert('athm_item_types')
			   ->setValue('name', $query->createParameter('new_item_type'));

		$query->setParameter('new_item_type', 'paper');
		$query->executeStatement();
		$query->setParameter('new_item_type', 'article');
		$query->executeStatement();
		$query->setParameter('new_item_type', 'book');
		$query->executeStatement();
		$query->setParameter('new_item_type', 'document');
		$query->executeStatement();
		$query->setParameter('new_item_type', 'presentation');
		$query->executeStatement();
		$query->setParameter('new_item_type', 'video');
		$query->executeStatement();
		$query->setParameter('new_item_type', 'audio');
		$query->executeStatement();

		// add default item folders
		$query = $this->connection->getQueryBuilder();
		$query->insert('athm_folders')
			->setValue('path', $query->createParameter('new_path'))
			->setValue('name', $query->createParameter('new_name'))
			->setValue('editable', $query->createParameter('new_editable'))
			->setValue('icon', $query->createParameter('new_icon'))
			->setValue('user_id', $query->createParameter('new_user_id'));

		$query->setParameter('new_path', 'inbox');
		$query->setParameter('new_name', 'Inbox');
		$query->setParameter('new_editable', false);
		$query->setParameter('new_icon', 'Inbox');
		$query->setParameter('new_user_id', $this->userId);
		$query->executeStatement();
		$query->setParameter('new_path', 'inbox:decide_later');
		$query->setParameter('new_name', 'Decide Later');
		$query->setParameter('new_editable', true);
		$query->setParameter('new_icon', 'Inbox');
		$query->setParameter('new_user_id', $this->userId);
		$query->executeStatement();
		$query->setParameter('new_path', 'library');
		$query->setParameter('new_name', 'Library');
		$query->setParameter('new_editable', false);
		$query->setParameter('new_icon', 'Bookshelf');
		$query->setParameter('new_user_id', $this->userId);
		$query->executeStatement();
		$query->setParameter('new_path', 'wastebasket');
		$query->setParameter('new_name', 'Wastebasket');
		$query->setParameter('new_editable', false);
		$query->setParameter('new_icon', 'Bookshelf');
		$query->setParameter('new_user_id', $this->userId);
		$query->executeStatement();

		// add default fields
		$query = $this->connection->getQueryBuilder();
		$query->insert('athm_fields')
			  ->setValue('name', $query->createParameter('new_field_name'))
			  ->setValue('type_hint', $query->createParameter('new_field_type_hint'));

		$query->setParameter('new_field_name', 'url');
		$query->setParameter('new_field_type_hint', 'string');
		$query->executeStatement();
		$query->setParameter('new_field_name', 'inbox_read');
		$query->setParameter('new_field_type_hint', 'bool');
		$query->executeStatement();
		$query->setParameter('new_field_name', 'inbox_importance');
		$query->setParameter('new_field_type_hint', 'int');
		$query->executeStatement();
		$query->setParameter('new_field_name', 'inbox_needs_review');
		$query->setParameter('new_field_type_hint', 'bool');
		$query->executeStatement();

		// add default contributors
		$query = $this->connection->getQueryBuilder();
		$query->insert('athm_contribn_types')
			  ->setValue('name', $query->createParameter('new_contribn_type'));

		$query->setParameter('new_contribn_type', 'author');
		$query->executeStatement();
		$query->setParameter('new_contribn_type', 'editor');
		$query->executeStatement();
	}
}