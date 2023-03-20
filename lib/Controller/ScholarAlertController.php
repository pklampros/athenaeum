<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ScholarAlertService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ScholarAlertController extends Controller {
	private ScholarAlertService $service;

	use Errors;

	public function __construct(IRequest $request,
								ScholarAlertService $service,
								?string $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(): DataResponse {
		return new DataResponse($this->service->findAll());
	}

	/**
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->find($id);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(string $scholarId, string $term): DataResponse {
		$importance = 0;
		$importanceDecided = false;
		return new DataResponse($this->service->create($scholarId, $term,
								$importance, $importanceDecided));
	}

	/**
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
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id);
		});
	}
}
