<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Tests\Unit\Controller;

use OCA\Athenaeum\Controller\ItemController;

use OCA\Athenaeum\Db\Item;
use OCA\Athenaeum\Service\ItemNotFound;

use OCA\Athenaeum\Service\ItemService;

use OCP\AppFramework\Http;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

class ItemControllerTest extends TestCase {
	protected ItemController $controller;
	protected string $userId = 'john';
	protected $service;
	protected $request;

	public function setUp(): void {
		parent::setUp();
		$this->request = $this->getMockBuilder(IRequest::class)->getMock();
		$this->service = $this->getMockBuilder(ItemService::class)
			->disableOriginalConstructor()
			->getMock();
		$this->controller = new ItemController($this->request, $this->service, $this->userId);
	}

	public function testUpdate(): void {
		$item = new Item();
		$this->service->expects($this->once())
			->method('update')
			->with($this->equalTo(3),
				$this->equalTo('title'),
				$this->equalTo(0),
				$this->equalTo(1),
				$this->equalTo($this->userId))
			->will($this->returnValue($item));

		$result = $this->controller->update(3, 'title', 0, 1);

		$this->assertEquals($item, $result->getData());
	}


	public function testUpdateNotFound(): void {
		// test the correct status code if no item is found
		$this->service->expects($this->once())
			->method('update')
			->will($this->throwException(new ItemNotFound()));

		$result = $this->controller->update(3, 'title', 0, 1);

		$this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
	}
}
