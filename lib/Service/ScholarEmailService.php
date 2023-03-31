<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Petros Koutsolampros <commits@pklampros.io>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Athenaeum\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Athenaeum\Db\ScholarEmail;
use OCA\Athenaeum\Db\ScholarEmailMapper;

class ScholarEmailService {
	private ScholarEmailMapper $mapper;

	public function __construct(ScholarEmailMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @return list<ScholarEmail>
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
			throw new ScholarEmailNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id): ScholarEmail {
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

	public function findBySubjectReceived(string $subject, \DateTime $received): ScholarEmail {
		try {
			return $this->mapper->findBySubjectReceived($subject, $received);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $subject, \DateTime $received,
						   int $scholarAlertId): ScholarEmail {
		try {
			$entity = new ScholarEmail();
			$entity->setSubject($subject);
			$entity->setReceived($received);
			$entity->setScholarAlertId($scholarAlertId);
			return $this->mapper->insert($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $id, string $subject, \DateTime $received,
						   int $scholarAlertId): ScholarEmail {
		try {
			$entity = $this->mapper->find($id);
			$entity->setSubject($subject);
			$entity->setReceived($received);
			$entity->setScholarAlertId($scholarAlertId);
			return $this->mapper->update($entity);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id): ScholarEmail {
		try {
			$entity = $this->mapper->find($id);
			$this->mapper->delete($entity);
			return $entity;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
