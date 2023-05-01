<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ItemService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ItemController extends Controller {
	private ItemService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
								ItemService $service,
								?string $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(
        int $limit = 50,
        int $offset = 0,
        ?bool $showAll = false,
        string $search = ''
	): DataResponse {
		return new DataResponse($this->service->findAll(
			$this->userId, $limit, $offset, $showAll, $search
		));
	}

	/**
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->find($id, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(string $title, int $itemTypeId): DataResponse {
		$currentTime = new \DateTime;
		return new DataResponse($this->service->create($title, $itemTypeId,
								$currentTime, $currentTime, $this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function createDetailed(): DataResponse {
		$currentTime = new \DateTime;
		return new DataResponse($this->service->createDetailed(
									$this->request->post['itemData'],
									$currentTime, $currentTime, $this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id, string $title, int $itemTypeId, \DateTime $dateAdded,
						   \DateTime $dateModified): DataResponse {
		return $this->handleNotFound(function () use ($id, $title, $itemTypeId, $dateAdded,
													  $dateModified) {
			return $this->service->update($id, $title, $itemTypeId, $dateAdded,
										  $dateModified, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id, $this->userId);
		});
	}
}
