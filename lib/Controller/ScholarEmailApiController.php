<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ScholarEmailService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;

class ScholarEmailApiController extends ApiController {
	private ScholarEmailService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
								ScholarEmailService $service,
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
	public function findBySubjectReceived(string $subject, string $received): DataResponse {
        // GET /scholarId/<id>
		$receivedDT = new \DateTime($received);
		return $this->handleNotFound(function () use ($subject, $receivedDT) {
            return $this->service->findBySubjectReceived($subject, $receivedDT);
        
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
	public function create(string $subject, string $received,
						   int $alertId): DataResponse {
		$receivedDT = new \DateTime($received);
		return new DataResponse($this->service->create($subject, $receivedDT,
								$alertId));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function update(int $id, string $subject, string $received,
						   int $alertId): DataResponse {
		$receivedDT = new \DateTime($received);
		return $this->handleNotFound(function () use ($id, $subject, $receivedDT,
									 $alertId) {
			return $this->service->update($id, $subject, $receivedDT,
										  $alertId);
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
