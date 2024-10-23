<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\AppInfo;

use OCA\Athenaeum\Service\UserInfoService;
use OCA\Athenaeum\Settings\PersonalSettingsForm;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IConfig;
use OCP\IServerContainer;

include_once __DIR__ . '/../../vendor/autoload.php';

class Application extends App implements IBootstrap {
	public const APP_ID = 'athenaeum';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);

		$container = $this->getContainer();

		/**
		 * Controllers
		 */
		$container->registerService('UserInfoService', function (IServerContainer $c): UserInfoService {
			return new UserInfoService(
				$c->get(IConfig::class),
				$c->get('appName')
			);
		});
	}

	public function register(IRegistrationContext $context): void {
		$context->registerDeclarativeSettings(
			PersonalSettingsForm::class
		);
		$context->registerEventListener(
			BeforeUserDeletedEvent::class,
			UserDeletedListener::class
		);
	}

	public function boot(IBootContext $context): void {
	}
}
