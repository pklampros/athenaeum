<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ItemFieldValueService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ItemFieldValueApiController extends ApiController {
	private ItemFieldValueService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
								ItemFieldValueService $service,
								?string $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function index(): DataResponse {
		return new DataResponse($this->service->findAll($this->userId));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function findByFieldId(int $itemId, int $fieldId, int $order): DataResponse {
		return $this->handleNotFound(function () use ($itemId, $fieldId, $order) {
            return $this->service->findByFieldId($itemId, $fieldId, $order);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function findByFieldName(int $itemId, string $fieldName, int $order): DataResponse {
		return $this->handleNotFound(function () use ($itemId, $fieldName, $order) {
			return $this->service->findByFieldName($itemId, $fieldName, $order);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->find($id, $this->userId);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function create(string $title, int $itemTypeId): DataResponse {
						   $currentTime = new \DateTime;
		return new DataResponse($this->service->create($title, $itemTypeId,
								$currentTime, $currentTime, $this->userId));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
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
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function setItemFieldValueFieldValue(int $itemId, string $fieldName,
									  string $fieldValue): DataResponse {
		return $this->handleNotFound(function () use ($itemId, $fieldName, $fieldValue) {
			return $this->service->setItemFieldValueFieldValue($itemId, $fieldName,
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
			return $this->service->delete($id, $this->userId);
		});
	}
}
