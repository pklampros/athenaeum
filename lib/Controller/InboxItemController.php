<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Controller;

use PhpMimeMailParser\Parser;
use OCA\Athenaeum\AppInfo\Application;
use OCA\Athenaeum\Service\InboxItemService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;

class InboxItemController extends Controller {
	private InboxItemService $inboxItemService;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
								InboxItemService $inboxItemService,
								?string $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->inboxItemService = $inboxItemService;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(
		string $folder = "",
        int $limit = 50,
        int $offset = 0,
        ?bool $showAll = false,
        string $search = ''
	): DataResponse {
		return new DataResponse($this->inboxItemService->findAll(
			$this->userId, $folder, $limit, $offset, $showAll, $search
		));
	}

	/**
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->inboxItemService->getWithDetails($id, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function getWithDetails(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->inboxItemService->getWithDetails($id, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function decideLater(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->inboxItemService->decideLater($id, $this->userId);
		});
	}

    public function hasEllipsis($text) {
        return str_contains($text, "...") || str_contains($text, "…");
	}

	public function cleanSafeLinks($href) {
		if (preg_match("/https:\/\/.*?\.safelinks.protection.outlook.com\/.*?url=(.*)&.*?/",
					   $href, $matches)) {
			$href = urldecode($matches[1]);
		}
		return $href;
	}

	public function getAlertData(\DOMDocument $doc, string $emailSubject) {
		$links = $doc->getElementsByTagName('a');
		$searchTerm = "";
		$alertId = "";
		$termIncomplete = false;

		foreach ($links as $link) {
			$href = $link->getAttribute('href');
			$href = $this->cleanSafeLinks($href);

			if (preg_match("/.*?\/scholar\?q=.*?/",
						   $href, $matches)) {
				$searchTerm = substr($link->textContent, 1, -1);
			}
			if (preg_match("/.*?\/scholar_alerts\?.*?cancel_alert_options.*?&alert_id=(.*?)&/",
						   $href, $matches)) {
				$alertId = $matches[1];
			}
		}
		if ($searchTerm == "") {
			if ($this->hasEllipsis($emailSubject)) {
				// full search term is not in the subject, search the body
				$boldTxts = $doc->getElementsByTagName('b');
				foreach ($boldTxts as $boldTxt) {
					if (preg_match("/(.*) - new results/",
								   $boldTxt->textContent, $matches)) {
						$searchTerm = $matches[1];
						if ($this->hasEllipsis($searchTerm)) {
							$termIncomplete = true;
						}
						break;
					}
				}
			} else if (preg_match("/Scholar Alert - \[ (.*) \]/",
								  $emailSubject, $matches)) {
				# up to 03/10/2017
				$searchTerm = $matches[1];
			} else if (preg_match("/\[ (.*) \] - new results/",
								  $emailSubject, $matches)) {
				# only on 05/10/2017
				$searchTerm = $matches[1];
			} else if (preg_match("/(.*) - new results/",
								  $emailSubject, $matches)) {
				# from 07/10/2017
				$searchTerm = $matches[1];
			}
		}
		if ($searchTerm == "") {
			// didn't find the true term, stick with the incomplete one
			$searchTerm = $incompleteSearchTerm;
		}
		
		if ($searchTerm == "") {
			// accept defeat, couldn't find the true term
			throw new \Exception( "Search term not found in email!" );
		}
		
		$cleanSearchTerm = $searchTerm;
		while (str_ends_with($cleanSearchTerm, " - new results")) {
			$cleanSearchTerm = substr($cleanSearchTerm, 0, -strlen(" - new results"));
		}

		if ($alertId == "") {
			throw new \Exception( "Alert ID not found in email!" );
		}
		return array(
			"alertId" => $alertId,
			"searchTerm" => $searchTerm,
			"cleanSearchTerm" => $cleanSearchTerm,
			"termIncomplete" => $termIncomplete
		);
	}

	public function getItems(\DOMDocument $doc) {
		$links = $doc->getElementsByTagName('a');
        $items = array();
		foreach ($links as $link) {
			$href = $link->getAttribute('href');
			$href = $this->cleanSafeLinks($href);

            $title = $link->textContent;
            $trueURL = "";
			if (preg_match("/.*?scholar_url\?url=(.*)&hl.*?/",
						   $href, $matches)) {
				$trueURL = urldecode($matches[1]);
			}
            if ($trueURL) {
                $authorsJournalDate = "";
                $excerpt = "";
                $nextSibling = $link->parentNode->nextSibling;
                $isDiv = $nextSibling->tagName == 'div';
                if ($isDiv) {
                    if (($nextSibling->hasAttribute('style')
							&& str_contains($nextSibling->getAttribute('style'), 'color'))
                        || ($nextSibling->hasChildNodes()
							&& $nextSibling->childNodes[0]->hasAttribute('color'))) {
                        $authorsJournalDate = $nextSibling->textContent;
                        $nextSibling = $nextSibling->nextSibling;
                        $isDiv = $nextSibling->tagName == 'div';
					}
                    if ($isDiv) {
                        $excerpt = $nextSibling->textContent;
					}
				}

                $authors = "";
                $journal = "";
                $published = "";

				// remove funky non-breaking unicode spaces
				$authorsJournalDate = preg_replace('~\x{00a0}~siu', ' ', $authorsJournalDate);

                $grps = preg_split("/\s-\s/", $authorsJournalDate);
                if (sizeof($grps) > 0) {
                    $authors = trim($grps[0]);
				}
                if (sizeof($grps) > 1) {
                    $journalDate = explode(",", $grps[1], 2);
                    if (sizeof($journalDate) == 2) {
                        $journal = trim($journalDate[0]);
                        $published = trim($journalDate[1]);
					}
                    if (sizeof($journalDate) == 1) {
						if (ctype_digit(trim($journalDate[0]))) {
							$published = trim($journalDate[0]);
						} else {
							$journal = trim($journalDate[0]);
						}
					}
				}

				// remove various space characters
				$excerpt = preg_replace('~\x{00a0}~siu', ' ', $excerpt);
				$excerpt = str_replace("\n", " ", $excerpt);
				$excerpt = str_replace("\r", " ", $excerpt);
				$excerpt = str_replace("  ", " ", $excerpt);

                array_push($items, array(
                    "url" => $trueURL,
                    "title" => $title,
                    "authors" => $authors,
                    "journal" => $journal,
                    "published" => $published,
                    "excerpt" => $excerpt
				));
			}
		}
        return $items;
	}

	/**
	 * @NoAdminRequired
	 */
	public function extractFromEML(): DataResponse {
		$fileCount = $this->request->post['fileCount'];
		$fileMetadata = json_decode($this->request->post['fileMetadata']);
		$responses = array();
		for ($i = 0; $i < $fileCount; $i++) {
			$newFile = $this->request->getUploadedFile("" . $i);
			$parser = new Parser();
			$parser->setText(file_get_contents($newFile['tmp_name']));

			$emailSubject = $parser->getHeader('subject');

			$result = array(
				"isResultsEmail" => false
			);

			if (preg_match("/Scholar Alert - \[ (.*) \]/",
						$emailSubject, $matches)) {
				# up to 03/10/2017
				$result["isResultsEmail"] = true;
			} else if (preg_match("/\[ (.*) \] - new results/",
								$emailSubject, $matches)) {
				# only on 05/10/2017
				$result["isResultsEmail"] = true;
			} else if (preg_match("/(.*) - new results/",
								$emailSubject, $matches)) {
				# from 07/10/2017
				$result["isResultsEmail"] = true;
			}
			
			if ($result["isResultsEmail"]) {
				$doc = new \DOMDocument('1.0', 'UTF-8');
				// the below xml hack is required to allow the domdocument to actually read UTF8
				// https://stackoverflow.com/questions/39148170/utf-8-with-php-domdocument-loadhtml
				$doc->loadHTML('<?xml encoding="UTF-8">' . $parser->getMessageBody('html'));
				$dateString = $parser->getHeader('date');
				$receivedDate = \DateTime::createFromFormat('D, d M Y H:i:s O', $dateString);
				if ($receivedDate === false) {
					throw new \Exception( "Can not parse string: $dateString" );
				}
				$alertData = $this->getAlertData($doc, $emailSubject);
				
				$result["subject"] = $emailSubject;
				$result["received"] = $receivedDate;
				$result["alertId"] = $alertData["alertId"];
				$result["searchTerm"] = $alertData["searchTerm"];
				$result["cleanSearchTerm"] = $alertData["cleanSearchTerm"];
				$result["termIncomplete"] = $alertData["termIncomplete"];
				$result["items"] = $this->getItems($doc);

				$response = $this->inboxItemService->createFromEML($result, $this->userId);
				$responses[$newFile['name']] = $response;
			}
		}
		
		if (count($responses) > 0) {
			return new DataResponse($responses);
		} else {
			return new DataResponse("No file sent", Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function toLibrary(): DataResponse {
		$currentTime = new \DateTime;
		$itemData = $this->request->post['itemData'];
		$id = $itemData['id'];
		return new DataResponse($this->inboxItemService->toLibrary(
									$id, $itemData, $currentTime, $currentTime,
									$this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(string $url, string $title, string $authors, string $journal,
						   string $published): DataResponse {
		$read = false;
		$importance = 0;
		$needsReview = false;
		
		return new DataResponse($this->inboxItemService->create($url, $title, $authors,
								$journal, $published, $read, $importance,
								$needsReview, $this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(int $id, string $url, string $title, string $authors, string $journal,
						   string $published, bool $read = false, int $importance = 0,
						   bool $needsReview = false): DataResponse {
		return $this->handleNotFound(function () use ($id, $url, $title, $authors, $journal,
													  $published, $read, $importance,
													  $needsReview) {
			return $this->inboxItemService->update($id, $url, $title, $authors,
										  $journal, $published, $read,
										  $importance, $needsReview, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->inboxItemService->delete($id, $this->userId);
		});
	}
}
