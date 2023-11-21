<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Service;

use OCP\IConfig;
use OCA\Athenaeum\Db\UserInfoMapper;

class UserInfoService {
    private IConfig $config;
	private UserInfoMapper $mapper;
    private string $appName;

    public function __construct(IConfig $config, UserInfoMapper $mapper, string $appName){
        $this->config = $config;
		$this->mapper = $mapper;
        $this->appName = $appName;
    }

    public function getUserValue(string $key, string $userId): string {
        return $this->config->getUserValue($userId, $this->appName, $key);
    }

    public function setUserValue(string $key, string $userId, string $value): void {
        $this->config->setUserValue($userId, $this->appName, $key, $value);
    }

	public function createDefaultData(string $userId) {
		$this->mapper->createDefaultData($userId);
	}
}