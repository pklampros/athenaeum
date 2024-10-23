<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\InboxItemService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class InboxItemApiController extends ApiController {
	private InboxItemService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
		InboxItemService $service,
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
	public function index(
		int $limit = 50,
		int $offset = 0,
		?bool $showAll = false,
		string $search = '',
	): DataResponse {
		return new DataResponse($this->service->findAll(
			$this->userId, $limit, $offset, $showAll, $search
		));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function findByUrl(string $url): DataResponse {
		// GET /url/<id>
		$decodedURL = urldecode(urldecode($url));
		return $this->handleNotFound(function () use ($decodedURL) {
			return $this->service->findByUrl($decodedURL);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->find($id, $this->userId);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function create(string $url, string $title, string $authors, string $journal,
		string $published, $read = false, $importance = 0,
		$needsReview = false): DataResponse {
		$decodedURL = urldecode(urldecode($url));
		return new DataResponse($this->service->create($decodedURL, $title, $authors,
			$journal, $published, $read, $importance,
			$needsReview, $this->userId));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function update(int $id, string $url, string $title, string $authors, string $journal,
		string $published, bool $read = false, int $importance = 0,
		bool $needsReview = false): DataResponse {
		$decodedURL = urldecode(urldecode($url));
		return $this->handleNotFound(function () use ($id, $decodedURL, $title, $authors, $journal,
			$published, $read, $importance,
			$needsReview) {
			return $this->service->update($id, $decodedURL, $title, $authors,
				$journal, $published, $read,
				$importance, $needsReview, $this->userId);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id, $this->userId);
		});
	}
}
