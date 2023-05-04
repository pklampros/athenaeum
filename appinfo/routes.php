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
		'item' => ['url' => '/items'],
		'item_api' => ['url' => '/api/0.1/items'],
		// 'item_field_value_api' => ['url' => '/api/0.1/item_field_values'],
		'scholar_item' => ['url' => '/scholar_items'],
		'scholar_item_api' => ['url' => '/api/0.1/scholar_items'],
		'scholar_alert_api' => ['url' => '/api/0.1/scholar_alerts'],
		'scholar_email_api' => ['url' => '/api/0.1/scholar_emails'],
		'scholar_email_item_api' => ['url' => '/api/0.1/scholar_email_items'],
		// 'contributor' => ['url' => '/contributors'],
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

		['name' => 'item#createDetailed',
			'url' => '/items/new/detailed',
			'verb' => 'POST'],

		// ['name' => 'item_field_value_api#findByFieldId',
		// 	'url' => '/api/0.1/item_field_values/itemId/{itemId}/fieldId/{fieldId}/order/{order}',
		// 	'verb' => 'GET'],
		// ['name' => 'item_field_value_api#findByFieldName',
		// 	'url' => '/api/0.1/item_field_values/itemId/{itemId}/fieldName/{fieldName}/order/{order}',
		// 	'verb' => 'GET'],

		['name' => 'scholar_alert_api#findByScholarId',
			'url' => '/api/0.1/scholar_alerts/scholarId/{scholarId}',
			'verb' => 'GET'],

		['name' => 'scholar_email_api#findBySubjectReceived',
			'url' => '/api/0.1/scholar_emails/subject/{subject}/received/{received}',
			'verb' => 'GET'],

		['name' => 'scholar_item#getWithDetails',
			'url' => '/scholar_items/details/{id}',
			'verb' => 'GET'],

		['name' => 'scholar_item_api#findByUrl',
			'url' => '/api/0.1/scholar_items/url/{url}',
			'verb' => 'GET', 'escape' => false],
		['name' => 'scholar_item#extractFromEML',
			'url' => '/scholar_items/extractFromEML',
			'verb' => 'POST'],

		['name' => 'item#getWithDetails',
			'url' => '/items/details/{id}',
			'verb' => 'GET'],

		['name' => 'scholar_email_item_api#findByEmailItem',
			'url' => '/api/0.1/scholar_email_items/scholarEmailId/{scholarEmailId}/scholarItemId/{scholarItemId}',
			'verb' => 'GET'],

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
		
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

		['name' => 'page#inbox', 'url' => '/inbox', 'verb' => 'GET'],
		['name' => 'page#showInboxItem',
			'url' => '/inbox/{inboxItemId}',
			'verb' => 'GET'],

		['name' => 'page#library', 'url' => '/library', 'verb' => 'GET'],
		['name' => 'page#showLibraryItem',
			'url' => '/library/{libraryItemId}',
			'verb' => 'GET'],
	]
];
