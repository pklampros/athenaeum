<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ScholarEmailItemService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;

class ScholarEmailItemApiController extends ApiController {
	private ScholarEmailItemService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
								ScholarEmailItemService $service,
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
	public function findByEmailItem(int $scholarEmailId, int $scholarItemId): DataResponse {
        // GET /emailId/<id>/itemId/<id>
		return $this->handleNotFound(function () use ($scholarEmailId, $scholarItemId) {
            return $this->service->findByEmailItem($scholarEmailId, $scholarItemId);
        
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
	public function create(int $scholarEmailId, int $scholarItemId,
						   string $excerpt): DataResponse {
		return new DataResponse($this->service->create($scholarEmailId,
								$scholarItemId, $excerpt));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function update(int $id, int $scholarEmailId, int $scholarItemId,
						   string $excerpt): DataResponse {
		return $this->handleNotFound(function () use ($id, $scholarEmailId,
									 $scholarItemId, $excerpt) {
			return $this->service->update($id, $scholarEmailId, $scholarItemId,
										  $excerpt);
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
