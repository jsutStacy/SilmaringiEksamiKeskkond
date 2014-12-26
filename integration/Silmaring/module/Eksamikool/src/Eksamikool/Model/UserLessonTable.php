<?php

namespace Eksamikool\Model;

use Zend\Db\TableGateway\TableGateway;

class UserLessonTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getUserLesson($userId, $lessonId) {
		$rowset = $this->tableGateway->select(array('user_id' => $userId, 'lesson_id' => $lessonId));

		return $rowset->current();
	}

	public function markLessonDone($userId, $lessonId) {
		$data = array(
			'user_id' => $userId,
			'lesson_id' => $lessonId,
			'done' => true,
		);
		$this->tableGateway->insert($data);
	}
}