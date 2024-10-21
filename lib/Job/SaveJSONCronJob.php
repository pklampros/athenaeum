<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Job;

use OCA\Athenaeum\Service\ItemService;
use OCP\BackgroundJob\QueuedJob;
use OCP\AppFramework\Utility\ITimeFactory;

class SaveJSONCronJob extends TimedJob {

    private ItemService $itemService;

    public function __construct(ItemService $service) {
        $this->itemService = $service;

        $this->setInterval(3600);

		$this->setTimeSensitivity(\OCP\BackgroundJob\IJob::TIME_INSENSITIVE);
    }

    protected function run($arguments) {
        $this->itemService->saveJSON(
			$arguments['itemId'], $arguments['userId']
		);
    }

}