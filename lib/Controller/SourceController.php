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
	private string $userId;

	use Errors;

	public function __construct(IRequest $request,
		SourceService $service,
		?string $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
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
	public function create(string $uid, string $sourceType, string $title,
		string $description): DataResponse {
		$importance = 0;
		return new DataResponse($this->service->create($uid, $sourceType,
			$importance, $title, $description, $this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id,
		int $importance, string $title,
		string $description): DataResponse {
		return $this->handleNotFound(function () use ($id,
			$importance, $title, $description) {
			return $this->service->update($id, $importance,
				$title, $description, $this->userId);
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
