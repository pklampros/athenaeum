<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Athenaeum\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
	'resources' => [
		'item' => ['url' => '/res/items'],
		'item_api' => ['url' => '/api/0.1/items'],
		'source_api' => ['url' => '/api/0.1/sources'],
		'contributor_api' => ['url' => '/api/0.1/contributors'],
		'contribution_api' => ['url' => '/api/0.1/contributions'],
	],
	'routes' => [
		['name' => 'item_api#findByUrl',
			'url' => '/api/0.1/items/url/{url}',
			'verb' => 'GET'],
		['name' => 'item_api#createWithUrl',
			'url' => '/api/0.1/items/createWithUrl',
			'verb' => 'POST'],
		['name' => 'item_api#setItemFieldValue',
			'url' => '/api/0.1/items/{itemId}/setField/{fieldName}/{fieldValue}',
			'verb' => 'GET', 'escape' => false],

		// ['name' => 'inbox_item#toLibrary',
		// 	'url' => '/inbox_items/tolibrary',
		// 	'verb' => 'POST'],

		['name' => 'source_api#findByUid',
			'url' => '/api/0.1/sources/uid/{uid}',
			'verb' => 'GET'],

		// ['name' => 'inbox_item#getWithDetails',
		// 	'url' => '/inbox_items/details/{id}',
		// 	'verb' => 'GET'],
		// ['name' => 'inbox_item#decideLater',
		// 	'url' => '/inbox_items/decide_later/{id}',
		// 	'verb' => 'GET'],

		// ['name' => 'inbox_item_api#findByUrl',
		// 	'url' => '/api/0.1/inbox_items/url/{url}',
		// 	'verb' => 'GET', 'escape' => false],
		['name' => 'inbox_item#extractFromEML',
			'url' => '/inbox_items/extractFromEML',
			'verb' => 'POST'],

		['name' => 'item#getWithDetails',
			'url' => '/items/details/{id}',
			'verb' => 'GET'],
		['name' => 'item#attachFile',
			'url' => '/items/attachFile',
			'verb' => 'POST'],

		['name' => 'contributor_api#findByFullFirstName',
			'url' => '/api/0.1/contributors/firstName/{firstName}',
			'verb' => 'GET'],
		['name' => 'contributor_api#findByFirstLastName',
			'url' => '/api/0.1/contributors/firstName/{firstName}/lastName/{lastName}',
			'verb' => 'GET'],

		['name' => 'contribution_api#findByItemContributor',
			'url' => '/api/0.1/contributions/itemId/{itemId}/contributorId/{contributorId}',
			'verb' => 'GET'],

		['name' => 'item_api#preflighted_cors', 'url' => '/api/0.1/{path}',
			'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']],

		['name' => 'contributor#findSimilar',
			'url' => '/contributors/similar',
			'verb' => 'POST'],
		['name' => 'contributor#freeSearch',
			'url' => '/contributors/search',
			'verb' => 'POST'],
		
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

		['name' => 'page#items', 'url' => '/items/{folder}', 'verb' => 'GET'],
		['name' => 'page#itemsDetails', 'url' => '/items/{folder}/{itemId}', 'verb' => 'GET'],

		// ['name' => 'page#inbox', 'url' => '/inbox', 'verb' => 'GET'],
		// ['name' => 'page#showInboxItem',
		// 	'url' => '/inbox/id={itemId}',
		// 	'verb' => 'GET'],
		
		// ['name' => 'page#inboxFolder', 'url' => '/inbox/{folder}', 'verb' => 'GET'],
		// ['name' => 'page#showInboxFolderItem',
		// 	'url' => '/inbox/{folder}/id={itemId}',
		// 	'verb' => 'GET'],

		// ['name' => 'page#library', 'url' => '/library', 'verb' => 'GET'],
		// ['name' => 'page#showLibraryItem',
		// 	'url' => '/library/{itemId}',
		// 	'verb' => 'GET'],
	]
];
