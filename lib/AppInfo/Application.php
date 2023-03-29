<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\AppInfo;

use OCP\AppFramework\App;

include_once __DIR__ . '/../../vendor/autoload.php';

class Application extends App {
	public const APP_ID = 'athenaeum';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

    public function register(IRegistrationContext $context): void {
        $context->registerEventListener(
            BeforeUserDeletedEvent::class,
            UserDeletedListener::class
        );
    }
}
