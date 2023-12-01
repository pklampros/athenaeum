<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class AppInfoApiController extends Controller {

	use Errors;

	public function __construct(IRequest $request) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function maxFileUploads(): DataResponse {
		return new DataResponse(ini_get('max_file_uploads'));
	}
}
