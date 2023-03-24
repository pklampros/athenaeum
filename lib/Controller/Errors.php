<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use Closure;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\Athenaeum\Service\EntityNotFound;

trait Errors {
	protected function handleNotFound(Closure $callback): DataResponse {
		try {
			return new DataResponse($callback());
		} catch (EntityNotFound $e) {
			$message = ['message' => $e->getMessage()];
			return new DataResponse($message, Http::STATUS_NOT_FOUND);
		}
	}
}
