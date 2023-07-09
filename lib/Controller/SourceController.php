<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\SourceService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class SourceController extends Controller {
	private SourceService $service;

	use Errors;

	public function __construct(IRequest $request,
								SourceService $service,
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
	public function create(string $uid, string $sourceType): DataResponse {
		$importance = 0;
		return new DataResponse($this->service->create($uid, $sourceType,
								$importance));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id, string $uid, string $sourceType,
						   int $importance): DataResponse {
		return $this->handleNotFound(function () use ($id, $uid, $sourceType,
									 $importance) {
			return $this->service->update($id, $uid, $sourceType,
										  $importance);
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
