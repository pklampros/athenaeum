<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ScholarItemService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ScholarItemController extends Controller {
	private ScholarItemService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
								ScholarItemService $service,
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
	public function create(string $url, string $title, string $authors, string $journal,
						   string $published): DataResponse {
		$read = false;
		$importance = 0;
		$needsReview = false;
		
		$res = $this->service->create($url, $title, $authors,
								$journal, $published, $read,
								$importance, $needsReview, $this->userId);
		

		return new DataResponse($res);
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id, string $url, string $title, string $authors, string $journal,
						   string $published, bool $read = false, int $importance = 0,
						   bool $needsReview = false): DataResponse {
		return $this->handleNotFound(function () use ($id, $url, $title, $authors, $journal,
													  $published, $read, $importance,
													  $needsReview) {
			return $this->service->update($id, $url, $title, $authors,
										  $journal, $published, $read,
										  $importance, $needsReview, $this->userId);
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
