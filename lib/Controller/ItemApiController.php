<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ItemService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ItemApiController extends ApiController {
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
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function index(
		string $folder = 'library',
		int $limit = 50,
		int $offset = 0,
		?bool $showAll = false,
		string $search = '',
	): DataResponse {
		return new DataResponse($this->itemService->findAll(
			$this->userId, $folder, $limit, $offset, $showAll, $search
		));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function findByUrl(string $url): DataResponse {
		// GET /url/<id>
		$decodedURL = urldecode(urldecode($url));
		return $this->handleNotFound(function () use ($decodedURL) {
			return $this->itemService->findByFieldValue('url', $decodedURL, $this->userId);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->itemService->find($id, $this->userId);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function create(string $title, int $itemTypeId, int $folderId): DataResponse {
		$currentTime = new \DateTime;
		return new DataResponse($this->itemService->create($title, $itemTypeId, $folderId,
			$currentTime, $currentTime, $this->userId));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function createWithUrl(string $title, int $itemTypeId, int $folderId, string $url): DataResponse {
		$currentTime = new \DateTime;
		$decodedURL = urldecode(urldecode($url));
		return new DataResponse($this->itemService->createWithUrl($title, $itemTypeId, $folderId,
			$currentTime, $currentTime, $decodedURL, $this->userId));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function update(int $id, string $title, int $itemTypeId, int $folderId, \DateTime $dateAdded,
		\DateTime $dateModified): DataResponse {
		return $this->handleNotFound(function () use ($id, $title, $itemTypeId, $folderId,
			$dateAdded, $dateModified) {
			return $this->itemService->update($id, $title, $itemTypeId, $folderId, $dateAdded,
				$dateModified, $this->userId);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function setItemFieldValue(int $itemId, string $fieldName,
		string $fieldValue): DataResponse {
		return $this->handleNotFound(function () use ($itemId, $fieldName, $fieldValue) {
			return $this->itemService->setItemFieldValue($itemId, $fieldName,
				$fieldValue, $this->userId);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->itemService->delete($id, $this->userId);
		});
	}
}
