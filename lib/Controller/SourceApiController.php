<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\SourceService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class SourceApiController extends ApiController {
	private SourceService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
		SourceService $service,
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
	public function findByUid(string $uid): DataResponse {
		// GET /uid/<id>
		return $this->handleNotFound(function () use ($uid) {
			return $this->service->findByUid($uid);
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
	public function create(string $scholarId, string $term, int $importance,
		bool $importanceDecided): DataResponse {
		return new DataResponse($this->service->create($scholarId, $term,
			$importance, $importanceDecided));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function update(string $scholarId, string $term, int $importance,
		bool $importanceDecided): DataResponse {
		return $this->handleNotFound(function () use ($id, $scholarId, $term,
			$importance, $importanceDecided) {
			return $this->service->update($id, $scholarId, $term,
				$importance, $importanceDecided);
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
