<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ScholarEmailService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ScholarEmailController extends Controller {
	private ScholarEmailService $service;

	use Errors;

	public function __construct(IRequest $request,
								ScholarEmailService $service,
								?string $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(): DataResponse {
		return new DataResponse($this->service->findAll($this->userId));
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
	public function create(string $subject, \DateTime $received,
						   int $alertId): DataResponse {
		return new DataResponse($this->service->create($subject, $received,
								$alertId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id, string $subject, \DateTime $received,
						   int $alertId): DataResponse {
		return $this->handleNotFound(function () use ($id, $subject, $received,
									 $alertId) {
			return $this->service->update($id, $subject, $received,
										  $alertId);
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
