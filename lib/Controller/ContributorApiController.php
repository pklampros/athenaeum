<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ContributorService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;

class ContributorApiController extends ApiController {
	private ContributorService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
								ContributorService $service,
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
	public function findByFullFirstName(string $firstName): DataResponse {
		// GET /firstName/<firstName>/lastName/<lastName>
		return $this->handleNotFound(function () use ($firstName) {
			return $this->service->findByFirstLastName($firstName, '');
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function findByFirstLastName(string $firstName, ?string $lastName = ''): DataResponse {
		// GET /firstName/<firstName>/lastName/<lastName>
		return $this->handleNotFound(function () use ($firstName, $lastName) {
			return $this->service->findByFirstLastName($firstName, $lastName);
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
	public function create(string $firstName, bool $lastNameIsFullName,
						   ?string $lastName): DataResponse {
		if ($lastName == null) {
			$lastName = '';
			$lastNameIsFullName = true;
		}
		return new DataResponse($this->service->create($firstName, $lastName,
								$lastNameIsFullName));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function update(int $id, string $firstName, bool $lastNameIsFullName,
	    				   ?string $lastName): DataResponse {
		if ($lastName == null) {
			$lastName = '';
			$lastNameIsFullName = true;
		}
		return $this->handleNotFound(function () use ($id, $firstName, $lastName,
									 $lastNameIsFullName) {
			return $this->service->update($id, $firstName, $lastName,
										  $lastNameIsFullName);
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
