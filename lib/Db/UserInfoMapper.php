<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\TTransactional;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class UserInfoMapper {

    use TTransactional;

    private IDBConnection $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

	// This function is meant to be run once per user

	public function createDefaultData(string $userId): Bool {
		return $this->atomic(function () use (&$userId) {

			// add default item types
			$query = $this->db->getQueryBuilder();
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
			$query = $this->db->getQueryBuilder();
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
			$query->setParameter('new_user_id', $userId);
			$query->executeStatement();
			$query->setParameter('new_path', 'inbox:decide_later');
			$query->setParameter('new_name', 'Decide Later');
			$query->setParameter('new_editable', true);
			$query->setParameter('new_icon', 'Inbox');
			$query->setParameter('new_user_id', $userId);
			$query->executeStatement();
			$query->setParameter('new_path', 'library');
			$query->setParameter('new_name', 'Library');
			$query->setParameter('new_editable', false);
			$query->setParameter('new_icon', 'Bookshelf');
			$query->setParameter('new_user_id', $userId);
			$query->executeStatement();
			$query->setParameter('new_path', 'wastebasket');
			$query->setParameter('new_name', 'Wastebasket');
			$query->setParameter('new_editable', false);
			$query->setParameter('new_icon', 'Bookshelf');
			$query->setParameter('new_user_id', $userId);
			$query->executeStatement();

			// add default fields
			$query = $this->db->getQueryBuilder();
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
			$query = $this->db->getQueryBuilder();
			$query->insert('athm_contribn_types')
				->setValue('name', $query->createParameter('new_contribn_type'));

			$query->setParameter('new_contribn_type', 'author');
			$query->executeStatement();
			$query->setParameter('new_contribn_type', 'editor');
			$query->executeStatement();
			
			return true;
		}, $this->db);
	}
}
