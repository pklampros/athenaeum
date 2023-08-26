<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ItemService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;

class ItemController extends Controller {
	private ItemService $itemService;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
								ItemService $itemService,
								?string $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->itemService = $itemService;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(
		string $folder = "",
        int $limit = 50,
        int $offset = 0,
        ?bool $showAll = false,
        string $search = ''
	): DataResponse {
		return new DataResponse($this->itemService->findAll(
			$this->userId, $folder, $limit, $offset, $showAll, $search
		));
	}

	/**
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->itemService->getWithDetails($id, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function getWithDetails(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->itemService->getWithDetails($id, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function attachFile(): DataResponse {
		$newFile = $this->request->getUploadedFile('file');
		$itemId = $this->request->post['item_id'];
		if (!$newFile) {
			return new DataResponse("No file sent", Http::STATUS_NOT_FOUND);
		}
		if (!$itemId) {
			return new DataResponse("No item id given", Http::STATUS_NOT_FOUND);
		}
		$fileName = $newFile['name'];
		$fileMime = $newFile['type'];
		$fileSize = $newFile['size'];
		$fileData = file_get_contents($newFile['tmp_name']);

		
		return $this->handleNotFound(function () use ($itemId, $fileName, $fileMime,
													  $fileSize, $fileData) {
			return $this->itemService->attachFile((int) $itemId, $fileName, $fileMime,
												  $fileSize, $fileData, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(string $title, int $itemTypeId, int $folderId): DataResponse {
		$currentTime = new \DateTime;
		return new DataResponse($this->itemService->create($title, $itemTypeId, $folderId,
								$currentTime, $currentTime, $this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function createDetailed(): DataResponse {
		$currentTime = new \DateTime;
		return new DataResponse($this->itemService->createDetailed(
									$this->request->post['itemData'],
									$currentTime, $currentTime, $this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id, string $title, int $itemTypeId, int $folderId,
						   \DateTime $dateAdded, \DateTime $dateModified): DataResponse {
		return $this->handleNotFound(function () use ($id, $title, $itemTypeId, $folderId,
													  $dateAdded, $dateModified) {
			return $this->itemService->update($id, $title, $itemTypeId, $folderId, $dateAdded,
										  $dateModified, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function changeFolder(): DataResponse {
		$id = $this->request->post['id'];
		$folder = $this->request->post['folder'];
		return $this->handleNotFound(function () use ($id, $folder) {
			return $this->itemService->changeFolder($id, $folder, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->itemService->delete($id, $this->userId);
		});
	}
}
