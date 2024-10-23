<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Settings;

use OCP\Settings\DeclarativeSettingsTypes;
use OCP\Settings\IDeclarativeSettingsForm;

class PersonalSettingsForm implements IDeclarativeSettingsForm {
	public function getSchema(): array {
		return [
			'id' => 'athenaeum_declarative_form',
			'priority' => 10,
			'section_type' => DeclarativeSettingsTypes::SECTION_TYPE_PERSONAL,
			'section_id' => 'athenaeum-personal',
			'storage_type' => DeclarativeSettingsTypes::STORAGE_TYPE_INTERNAL,
			'title' => 'File and attachment settings',
			'description' => 'Settings for dealing with the filesystem',
			'doc_url' => '', // NcSettingsSection doc_url for documentation or help page, empty string if not needed
			'fields' => [
				[
					'id' => 'json_export_frequency',
					'title' => 'JSON Export Frequency',
					'description' => 'How often to extract JSON to the filesystem',
					'type' => DeclarativeSettingsTypes::RADIO,
					'default' => 'never',
					'options' => [
						[
							'name' => 'Every time a change occurs',
							'value' => 'onmodify'
						],
						[
							'name' => 'With cron runs (set by the admin)',
							'value' => 'cron'
						],
						[
							'name' => 'Never',
							'value' => 'never'
						],
					],
				],
			],
		];
	}
}
