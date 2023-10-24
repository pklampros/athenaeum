<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\FolderService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class FolderController extends Controller {
	private FolderService $service;
	private string $userId;

	use Errors;

	public function __construct(IRequest $request,
								FolderService $service,
								string $userId) {
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
			return $this->service->find($id);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(string $uid, string $FolderType): DataResponse {
		$importance = 0;
		return new DataResponse($this->service->create($uid, $FolderType,
								$importance));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id, string $uid, string $FolderType,
						   int $importance): DataResponse {
		return $this->handleNotFound(function () use ($id, $uid, $FolderType,
									 $importance) {
			return $this->service->update($id, $uid, $FolderType,
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
