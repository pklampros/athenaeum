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
			$table->addUniqueConstraint(['name']);
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
			$table->addUniqueConstraint(['name']);
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
				'notnull' => true,
				'default' => 0
			]);

			$table->setPrimaryKey(['id']);

			$table->addForeignKeyConstraint('athm_items', ['item_id'], ['id'], [],
											'item_id_fk');
			$table->addForeignKeyConstraint('athm_contributors', ['contributor_id'], ['id'], [],
											'contributor_id_fk');
			$table->addForeignKeyConstraint('athm_contribn_types', ['contribution_type_id'], ['id'], [],
											'contribution_type_id_fk');
			$table->addUniqueConstraint(['item_id', 'contributor_id', 'contribution_type_id']);

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
				'notnull' => false,
				'length' => 200
			]);
			$table->addColumn('last_name_is_full_name', 'boolean', [
				'notnull' => false, # null is used for false
				'default' => false
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
			$table->addUniqueConstraint(['name']);
		}

		if (!$schema->hasTable('athm_folders')) {
			$table = $schema->createTable('athm_folders');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('path', 'string', [
				'notnull' => true,
				'length' => 200
			]);

			$table->setPrimaryKey(['id']);
			$table->addUniqueConstraint(['path']);
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
			$table->addColumn('folder_id', 'integer', [
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
			$table->addForeignKeyConstraint('athm_item_types', ['item_type_id'], ['id'], [],
											'item_type_id_fk');
			$table->addForeignKeyConstraint('athm_folders', ['folder_id'], ['id'], [],
											'item_folder_id_fk');
		}

		if (!$schema->hasTable('athm_item_field_values')) {
			$table = $schema->createTable('athm_item_field_values');
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
			$table->addColumn('order', 'integer', [
				'notnull' => true,
				'default' => 0
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
			$table->addUniqueConstraint(['item_id', 'field_id', 'order']);
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
			$table->addUniqueConstraint(['item_id', 'tag_id']);
		}

		if (!$schema->hasTable('athm_sources')) {
			$table = $schema->createTable('athm_sources');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('uid', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('source_type', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('importance', 'integer', [
				'notnull' => true,
				'default' => 0
			]);

			$table->setPrimaryKey(['id']);
			$table->addUniqueConstraint(['uid']);
		}

		if (!$schema->hasTable('athm_item_sources')) {
			$table = $schema->createTable('athm_item_sources');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('item_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('source_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('extra', 'string', [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['id']);
			$table->addForeignKeyConstraint('athm_items', ['item_id'], ['id'], [],
											'item_id_fk');
			$table->addForeignKeyConstraint('athm_sources', ['source_id'], ['id'], [],
											'source_id_fk');
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
