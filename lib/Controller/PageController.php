<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

class PageController extends Controller {
	public function __construct(IRequest $request) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): TemplateResponse {
		Util::addScript(Application::APP_ID, 'athenaeum-main');

		return new TemplateResponse(Application::APP_ID, 'main');
	}

	// The rest of the functions here do not really do anything, they just
	// call upon the index() function to render the page. The data
	// transmitted back to the client is exactly the same for all, and it
	// is the client's responsibility to render the page based on what it
	// already has, and the url. The functions are only placed here to
	// allow the routers to point to valid urls, and not throw server errors.

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * 
	 * @return TemplateResponse
	 */
	public function showScholarItem(int $scholarItemId): TemplateResponse {
		return $this->index();
	}
}
