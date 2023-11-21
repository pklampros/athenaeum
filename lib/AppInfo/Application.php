<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\AppInfo;

use OCA\Athenaeum\Service\UserInfoService;
use OCP\AppFramework\App;
use OCP\IConfig;
use OCP\IServerContainer;

include_once __DIR__ . '/../../vendor/autoload.php';

class Application extends App {
	public const APP_ID = 'athenaeum';

	public function __construct() {
		parent::__construct(self::APP_ID);

        $container = $this->getContainer();

        /**
         * Controllers
         */
        $container->registerService('UserInfoService', function(IServerContainer $c): UserInfoService {
            return new UserInfoService(
                $c->get(IConfig::class),
                $c->get('appName')
            );
        });
	}

    public function register(IRegistrationContext $context): void {
        $context->registerEventListener(
            BeforeUserDeletedEvent::class,
            UserDeletedListener::class
        );
    }
}
