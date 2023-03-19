<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Migration;

use Closure;
use OCP\IDBConnection;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000000Date20181013124731 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('athm_contribn_types')) {
			$table = $schema->createTable('athm_contribn_types');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				   'notnull' => true,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length' => 200
			]);

			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('athm_item_types')) {
			$table = $schema->createTable('athm_item_types');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true
			]);

			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('athm_contributions')) {
			$table = $schema->createTable('athm_contributions');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('item_id', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('contributor_id', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('contributor_name_display', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('contribution_type_id', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('contribution_order', 'integer', [
				'notnull' => true
			]);

			$table->setPrimaryKey(['id']);

			$table->addForeignKeyConstraint('athm_items', ['item_id'], ['id'], [],
											'item_id_fk');
			$table->addForeignKeyConstraint('athm_contributors', ['contributor_id'], ['id'], [],
											'contributor_id_fk');
			$table->addForeignKeyConstraint('athm_contribn_types', ['contribution_type_id'], ['id'], [],
											'contribution_type_id_fk');

		}

		if (!$schema->hasTable('athm_contributors')) {
			$table = $schema->createTable('athm_contributors');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('first_name', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('last_name', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('first_name_is_full_name', 'boolean', [
				'notnull' => false # null is used for false
			]);
			
			$table->setPrimaryKey(['id']);
		}


		if (!$schema->hasTable('athm_fields')) {
			$table = $schema->createTable('athm_fields');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('type_hint', 'string', [
				'notnull' => true,
				'length' => 200
			]);

			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('athm_items')) {
			$table = $schema->createTable('athm_items');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true
			]);
			$table->addColumn('title', 'string', [
				'notnull' => true
			]);
			$table->addColumn('item_type_id', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('date_added', 'datetime', [
				'notnull' => true
			]);
			$table->addColumn('date_modified', 'datetime', [
				'notnull' => true
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 200,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'items_user_id_index');
			//$table->addForeignKeyConstraint('athm_item_types', ['item_type_id'], ['id'], [],
			//								'item_type_id_fk');
		}

		if (!$schema->hasTable('athm_item_data')) {
			$table = $schema->createTable('athm_item_data');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('item_id', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('field_id', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('value', 'text', [
				'notnull' => true,
				'default' => ''
			]);

			$table->setPrimaryKey(['id']);
			$table->addForeignKeyConstraint('athm_items', ['item_id'], ['id'], [],
											'item_id_fk');
			$table->addForeignKeyConstraint('athm_fields', ['field_id'], ['id'], [],
											'field_id_fk');
		}

		if (!$schema->hasTable('athm_item_tags')) {
			$table = $schema->createTable('athm_item_tags');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('item_id', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('tag_id', 'integer', [
				'notnull' => true
			]);

			$table->setPrimaryKey(['id']);
			$table->addForeignKeyConstraint('athm_items', ['item_id'], ['id'], [],
											'item_id_fk');
			$table->addForeignKeyConstraint('athm_tags', ['tag_id'], ['id'], [],
											'tag_id_fk');
		}

		if (!$schema->hasTable('athm_schlr_alerts')) {
			$table = $schema->createTable('athm_schlr_alerts');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('scholarId', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('term', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('importance', 'integer', [
				'notnull' => true,
				'default' => 0
			]);
			$table->addColumn('importanceDecided', 'boolean', [
				'notnull' => false # null is used for false
			]);

			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('athm_schlr_emails')) {
			$table = $schema->createTable('athm_schlr_emails');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('subject', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('received', 'datetime', [
				'notnull' => true
			]);
			$table->addColumn('fromAddress', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('toAddress', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('scholar_alert_id', 'integer', [
				'notnull' => true
			]);

			$table->setPrimaryKey(['id']);
			$table->addForeignKeyConstraint('athm_schlr_alerts', ['scholar_alert_id'], ['id'], [],
											'scholar_alert_id_fk');
		}

		if (!$schema->hasTable('athm_schlr_email_items')) {
			$table = $schema->createTable('athm_schlr_email_items');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('scholar_email_id', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('scholar_item_id', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('excerpt', 'string', [
				'notnull' => true,
				'length' => 200,
			]);

			$table->setPrimaryKey(['id']);
			$table->addForeignKeyConstraint('athm_schlr_emails', ['scholar_email_id'], ['id'], [],
											'email_id_fk');
			$table->addForeignKeyConstraint('athm_schlr_items', ['scholar_item_id'], ['id'], [],
											'item_id_fk');
		}

		if (!$schema->hasTable('athm_schlr_items')) {
			$table = $schema->createTable('athm_schlr_items');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('url', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('title', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('authors', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('journal', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('published', 'datetime', [
				'notnull' => true
			]);
			$table->addColumn('read', 'boolean', [
				'notnull' => false # null is used for false
			]);
			$table->addColumn('importance', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('needsReview', 'boolean', [
				'notnull' => false # null is used for false
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 200,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'schlr_items_user_id_index');
		}

		if (!$schema->hasTable('athm_tags')) {
			$table = $schema->createTable('athm_tags');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length' => 200
			]);

			$table->setPrimaryKey(['id']);
		}
		
		return $schema;
	}
}
