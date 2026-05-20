<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\ItemService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\IRequest;
use OCP\IConfig;
use OCP\IURLGenerator;

class ItemFeedApiController extends ApiController {
	private ItemService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
        private IConfig $config,
        private IURLGenerator $urlGenerator,
		ItemService $service,
		?string $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
	}

	private function getAsXML(array $items, string $token, $userId): string {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0"><channel>';
        $xml .= '<title>Athenaeum feed</title>';
		$feedUrl = $this->urlGenerator->linkToRouteAbsolute(
            'athenaeum.item_feed_api.feed',
            ['token' => $token]
        );
        $xml .= '<link>' . htmlspecialchars($feedUrl) . '</link>';

		if (!empty($items)) {
			$latest = max(array_map(fn($i) => $i->getDateAdded(), $items));
			$xml .= '<lastBuildDate>' . date(DATE_RSS, date_timestamp_get($latest)) . '</lastBuildDate>';
		}

		foreach ($items as $item) {

			$details = $this->service->getWithDetails($item->getId(), $userId);
			$sourceInfo = $details->getSourceInfo();
			$itemAuthors = 'Unknown';
			$excerpts = "Excerpts:<br><ul>";
			foreach ($sourceInfo as $sourceInfoData) {
				$extraItemData = $sourceInfoData["extra_item_data"];
				$itemAuthors = $extraItemData["authors"];
				$itemExcerpt = $extraItemData["excerpt"];
				$excerpts .= '<li>' . $itemExcerpt . '</li>';
			}
			$excerpts .= "</ul>";

            $xml .= '<item>';
            $xml .= '<title>';
			$xml .= htmlspecialchars($item->getTitle());
			$xml .= ' (' . $itemAuthors . ')';
			$xml .= '</title>';
			$itemUrl = $this->urlGenerator->linkToRouteAbsolute(
				'athenaeum.page.itemsDetails', [
				 	'folder' => 'inbox',
				 	'itemId' => $item->getId()
				 ]
			);
            $xml .= '<link>' . htmlspecialchars($itemUrl) . '</link>';

			$description = '';
			// $description .= '<br><br>';
			$description .= $excerpts;
			$description .= '<br>';

			$moveToInboxURL = $this->urlGenerator->linkToRouteAbsolute(
				'athenaeum.item.changeFolderConfirm', [
					'itemId' => $item->getId(),
					'folder' => 'library'
				]
			);
			$description .= '<br><a href=' . $moveToInboxURL . '>Add to library</a>';


			$moveToDecideLaterURL = $this->urlGenerator->linkToRouteAbsolute(
				'athenaeum.item.changeFolderConfirm', [
					'itemId' => $item->getId(),
					'folder' => 'inbox:decide_later'
				]
			);
			$description .= '<br><a href=' . $moveToDecideLaterURL . '>Decide later</a>';

			$moveToWastebasketURL = $this->urlGenerator->linkToRouteAbsolute(
				'athenaeum.item.changeFolderConfirm', [
					'itemId' => $item->getId(),
					'folder' => 'wastebasket'
				]
			);
			$description .= '<br><a href=' . $moveToWastebasketURL . '>Delete</a>';
            $xml .= '<description><![CDATA[' . $description . ']]></description>';
            $xml .= '<pubDate>' . date(DATE_RSS, date_timestamp_get($item->getDateAdded())) . '</pubDate>';
            $xml .= '<guid isPermaLink="false">' . $item->getId() . '</guid>';
            $xml .= '</item>';
        }

        $xml .= '</channel></rss>';
        return $xml;
    }

	/**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function feed(string $token, int $nItems = 30, string $orderBy='-da'): DataDisplayResponse {
		$userIds = $this->config->getUsersForUserValue('athenaeum', 'feed_token', $token);
        $userId = $userIds[0] ?? null;
        if ($userId === null) {
            return new DataDisplayResponse('Not found', Http::STATUS_NOT_FOUND);
        }

		$items = $this->service->findAll(
			$userId, 'inbox', min(max($nItems, 1), 70), 0, $orderBy, '', false
		);
        $xml = $this->getAsXML($items['items'], $token, $userId);

        return new DataDisplayResponse($xml, 200,
            ['Content-Type' => 'application/rss+xml; charset=utf-8']
		);
	}
}
