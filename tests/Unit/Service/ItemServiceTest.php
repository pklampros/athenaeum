<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Tests\Unit\Service;

use OCA\Athenaeum\Db\Item;
use OCA\Athenaeum\Db\ItemMapper;

use OCA\Athenaeum\Service\ItemNotFound;

use OCA\Athenaeum\Service\ItemService;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit\Framework\TestCase;

class ItemServiceTest extends TestCase {
	private ItemService $service;
	private string $userId = 'john';
	private $mapper;

	public function setUp(): void {
		$this->mapper = $this->getMockBuilder(ItemMapper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->service = new ItemService($this->mapper);
	}

	public function testUpdate(): void {
		// the existing item
		$item = Item::fromRow([
			'id' => 3
		]);

		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3),
				$this->equalTo($this->userId))
			->will($this->returnValue($item));

		// the item when updated
		$updatedItem = Item::fromRow(['id' => 3]);
		$updatedItem->setTitle('title');
		$updatedItem->setItemTypeId(2);
		$updatedItem->setFolderId(3);
		$currentDate = new \DateTime();
		$updatedItem->setDateModified($currentDate);
		$updatedItem->setUserId($this->userId);

		$this->mapper->expects($this->once())
			->method('update')
			->with($this->equalTo($updatedItem))
			->will($this->returnValue($updatedItem));

		$result = $this->service->update(3, 'title', 2, 3,
			$this->userId, $currentDate);

		$this->assertEquals($updatedItem, $result);
	}

	public function testUpdateNotFound(): void {
		$this->expectException(ItemNotFound::class);
		// test the correct status code if no item is found
		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3),
				$this->equalTo($this->userId))
			->will($this->throwException(new DoesNotExistException('')));

		$result = $this->service->update(3, 'title', 0, 1,
			$this->userId);
	}
}
