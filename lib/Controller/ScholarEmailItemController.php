<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ItemService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ItemController extends Controller {
	private ItemService $service;

	use Errors;

	public function __construct(IRequest $request,
								ItemService $service,
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
	public function create(int $emailId, int $itemId, string $excerpt): DataResponse {
		return new DataResponse($this->service->create($emailId, $itemId, $excerpt));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id, int $emailId, int $itemId, string $excerpt): DataResponse {
		return $this->handleNotFound(function () use ($id, $emailId, $itemId, $excerpt) {
			return $this->service->update($id, $emailId, $itemId, $excerpt);
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
