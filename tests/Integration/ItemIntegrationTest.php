<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Tests\Integration\Controller;

use OCA\Athenaeum\Controller\ItemController;
use OCA\Athenaeum\Db\Item;
use OCA\Athenaeum\Db\ItemMapper;

use OCP\AppFramework\App;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

class ItemIntegrationTest extends TestCase {
	private ItemController $controller;
	private QBMapper $mapper;
	private string $userId = 'john';

	public function setUp(): void {
		$app = new App('athenaeum');
		$container = $app->getContainer();

		// only replace the user id
		$container->registerService('userId', function () {
			return $this->userId;
		});

		// we do not care about the request but the controller needs it
		$container->registerService(IRequest::class, function () {
			return $this->createMock(IRequest::class);
		});

		$this->controller = $container->get(ItemController::class);
		$this->mapper = $container->get(ItemMapper::class);
	}

	public function testUpdate(): void {
		// create a new item that should be updated
		$item = new Item();
		$item->setTitle('old_title');
		$item->setContent('old_content');
		$item->setUserId($this->userId);

		$id = $this->mapper->insert($item)->getId();

		// fromRow does not set the fields as updated
		$updatedItem = Item::fromRow([
			'id' => $id,
			'user_id' => $this->userId
		]);
		$updatedItem->setContent('content');
		$updatedItem->setTitle('title');

		$result = $this->controller->update($id, 'title', 'content');

		$this->assertEquals($updatedItem, $result->getData());

		// clean up
		$this->mapper->delete($result->getData());
	}
}
