<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Athenaeum\Db\ScholarAlert;
use OCA\Athenaeum\Db\ScholarAlertMapper;

class ScholarAlertService {
	private ScholarAlertMapper $mapper;

	public function __construct(ScholarAlertMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<ScholarAlert>
	 */
	public function findAll(): array {
		return $this->mapper->findAll();
	}

	/**
	 * @return never
	 */
	private function handleException(Exception $e) {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new ScholarAlertNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id): ScholarAlert {
		try {
			return $this->mapper->find($id);

			// in order to be able to plug in different storage backends like files
		// for instance it is a good idea to turn storage related exceptions
		// into service related exceptions so controllers and service users
		// have to deal with only one type of exception
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $scholarId, string $term, int $importance,
						   bool $importanceDecided): ScholarAlert {
		try {
			$item = new ScholarAlert();
			$item->setScholarId($scholarId);
			$item->setTerm($term);
			$item->setImportance($importance);
			$item->setImportanceDecided($importanceDecided);
			return $this->mapper->insert($item);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $id, string $scholarId, string $term, int $importance,
						   bool $importanceDecided): ScholarAlert {
		try {
			$item = $this->mapper->find($id);
			$item->setScholarId($scholarId);
			$item->setTerm($term);
			$item->setImportance($importance);
			$item->setImportanceDecided($importanceDecided);
			return $this->mapper->update($item);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id): ScholarAlert {
		try {
			$item = $this->mapper->find($id);
			$this->mapper->delete($item);
			return $item;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
