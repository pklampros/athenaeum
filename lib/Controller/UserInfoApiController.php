<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\UserInfoService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class UserInfoApiController extends ApiController {
	private UserInfoService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
		UserInfoService $service,
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
	public function userInit(): DataResponse {
		$uis = \OC::$server->query(UserInfoService::class);
		$userDBID = $uis->getUserValue('dbid', $this->userId);
		if ($userDBID == '') {
			// The purpose of this code is to create fields and
			// values in the database that are default per user
			$this->service->createDefaultData($this->userId);
			$uis->setUserValue('dbid', $this->userId, uniqid());
			$userDBID = $uis->getUserValue('dbid', $this->userId);
			// Transaction has failed don't set a dbid
		}
		return new DataResponse($userDBID);
	}
}
