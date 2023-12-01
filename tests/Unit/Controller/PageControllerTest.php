<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Tests\Unit\Controller;

use OCA\Athenaeum\Controller\PageController;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;

use PHPUnit\Framework\TestCase;

class PageControllerTest extends TestCase {
	private PageController $controller;

	public function setUp(): void {
		$request = $this->getMockBuilder(\OCP\IRequest::class)->getMock();
		$initialState = $this->getMockBuilder(IInitialState::class)->getMock();
		$this->controller = new PageController($request, $initialState);
	}

	public function testIndex(): void {
		$result = $this->controller->index();

		$this->assertEquals('main', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}
}
