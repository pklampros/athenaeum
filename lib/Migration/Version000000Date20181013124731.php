<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

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
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addUniqueConstraint(['name']);

			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_contribn_types_user_id_fk');
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
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addUniqueConstraint(['name']);

			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_item_types_user_id_fk');
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
			$table->addColumn('date_added', 'datetime', [
				'notnull' => true
			]);
			$table->addColumn('date_modified', 'datetime', [
				'notnull' => true
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);
			
			$table->setPrimaryKey(['id']);

			$table->addIndex(['user_id'], 'athm_contributors_user_id_idx');

			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_contributors_user_id_fk');
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
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addUniqueConstraint(['name']);

			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_fields_user_id_fk');
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
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('editable', 'boolean', [
				'notnull' => false, # null is used for false
				'default' => false
			]);
			$table->addColumn('icon', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addUniqueConstraint(['path', 'user_id']);

			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_folders_user_id_fk');
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
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addIndex(['user_id'], 'athm_items_user_id_idx');

			$table->addForeignKeyConstraint($schema->getTable('athm_item_types'),
				['item_type_id'], ['id'], [], 'athm_item_type_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('athm_folders'),
				['folder_id'], ['id'], [], 'athm_item_folder_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_items_user_id_fk');
		}
		
		if (!$schema->hasTable('athm_item_attchm')) {
			$table = $schema->createTable('athm_item_attchm');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('item_id', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('path', 'string', [
				'notnull' => true
			]);
			$table->addColumn('mime_type', 'string', [
				'notnull' => false
			]);
			$table->addColumn('notes', 'string', [
				'notnull' => false
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addUniqueConstraint(['item_id', 'path']);

			$table->addForeignKeyConstraint($schema->getTable('athm_items'),
				['item_id'], ['id'], [], 'athm_item_attchm_item_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_attchm_user_id_fk');
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
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addUniqueConstraint(['item_id', 'contributor_id', 'contribution_type_id']);

			$table->addForeignKeyConstraint($schema->getTable('athm_items'),
				['item_id'], ['id'], [], 'athm_contribution_item_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('athm_contributors'),
				['contributor_id'], ['id'], [], 'athm_contributor_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('athm_contribn_types'),
				['contribution_type_id'], ['id'], [], 'athm_contribution_type_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_contributions_user_id_fk');
		}
		
		if (!$schema->hasTable('athm_item_rel_types')) {
			$table = $schema->createTable('athm_item_rel_types');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addUniqueConstraint(['name']);

			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_item_rel_types_user_id_fk');
		}

		if (!$schema->hasTable('athm_item_item_rel')) {
			$table = $schema->createTable('athm_item_item_rel');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('item_id_a', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('item_id_b', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('rel_type_id', 'integer', [
				'notnull' => true
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addUniqueConstraint(['item_id_a', 'item_id_b', 'rel_type_id']);

			$table->addForeignKeyConstraint($schema->getTable('athm_items'),
				['item_id_a'], ['id'], [], 'athm_item_rel_item_id_a_fk');
			$table->addForeignKeyConstraint($schema->getTable('athm_items'),
				['item_id_b'], ['id'], [], 'athm_item_rel_item_id_b_fk');
			$table->addForeignKeyConstraint($schema->getTable('athm_item_rel_types'),
				['rel_type_id'], ['id'], [], 'athm_item_rel_type_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_item_item_rel_user_id_fk');
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
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addUniqueConstraint(['item_id', 'field_id', 'order']);

			$table->addForeignKeyConstraint($schema->getTable('athm_items'),
				['item_id'], ['id'], [], 'athm_itm_fld_values_itm_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('athm_fields'),
				['field_id'], ['id'], [], 'athm_itm_fld_values_fld_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_itm_fld_values_user_id_fk');
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
			$table->addColumn('colour', 'string', [
				'notnull' => true,
				'length' => 32,
				'default' => 'default'
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addUniqueConstraint(['name', 'user_id']);

			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_tags_user_id_fk');
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
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addUniqueConstraint(['item_id', 'tag_id']);

			$table->addForeignKeyConstraint($schema->getTable('athm_items'),
				['item_id'], ['id'], [], 'athm_item_tags_item_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('athm_tags'),
				['tag_id'], ['id'], [], 'athm_item_tags_tag_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_item_tags_user_id_fk');
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
			$table->addColumn('title', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('description', 'string', [
				'notnull' => false,
			]);
			$table->addColumn('date_added', 'datetime', [
				'notnull' => true
			]);
			$table->addColumn('date_modified', 'datetime', [
				'notnull' => true
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);
			$table->addUniqueConstraint(['uid']);
			$table->addIndex(['user_id'], 'athm_sources_user_id_idx');
			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_sources_user_id_fk');
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
			$table->addColumn('extra', 'json', [
				'notnull' => true,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);

			$table->setPrimaryKey(['id']);

			$table->addForeignKeyConstraint($schema->getTable('athm_items'),
				['item_id'], ['id'], [], 'athm_item_sources_item_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('athm_sources'),
				['source_id'], ['id'], [], 'athm_item_sources_source_id_fk');
			$table->addForeignKeyConstraint($schema->getTable('users'),
				['user_id'], ['uid'], [], 'athm_item_sources_user_id_fk');
		}
		
		return $schema;
	}
}
