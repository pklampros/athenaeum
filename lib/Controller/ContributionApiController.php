<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ContributionService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ContributionApiController extends ApiController {
	private ContributionService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
		ContributionService $service,
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
		return new DataResponse($this->service->findAll());
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function findByItemContributor(string $itemId, string $contributorId): DataResponse {
		// GET /itemId/<itemId>/contributorId/<contributorId>
		return $this->handleNotFound(function () use ($itemId, $contributorId) {
			return $this->service->findByItemContributor($itemId, $contributorId);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->find($id);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function create(int $itemId, int $contributorId,
		string $contributorNameDisplay, int $contributionTypeId,
		int $contributionOrder): DataResponse {
		return new DataResponse($this->service->create($itemId, $contributorId,
			$contributorNameDisplay, $contributionTypeId,
			$contributionOrder));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function update(int $id, int $itemId, int $contributorId,
		string $contributorNameDisplay, int $contributionTypeId,
		int $contributionOrder): DataResponse {
		return $this->handleNotFound(function () use ($id, $itemId, $contributorId,
			$contributorNameDisplay, $contributionTypeId) {
			return $this->service->update($id, $itemId, $contributorId,
				$contributorNameDisplay, $contributionTypeId,
				$contributionOrder);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id);
		});
	}
}
