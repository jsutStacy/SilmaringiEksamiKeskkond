<?php

namespace EksamiKeskkond\Model;

use Zend\Db\TableGateway\TableGateway;

class UserCourseTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getCourseParticipants($courseId) {
		$userIds = array();
		$resultSet = $this->tableGateway->select(array('course_id' => $courseId));

		foreach ($resultSet as $row) {
			$userIds[] = $row->user_id;
		}
		return $userIds;
	}

	public function getCourseByUserId($userId) {
		$rowset = $this->tableGateway->select(array('user_id' => $userId));

		return $rowset->current();
	}

	public function getAllCoursesByUserId($userId) {
		$courseIds = array();
		$resultSet = $this->tableGateway->select(array('user_id' => $userId));

		foreach ($resultSet as $row) {
			$courseIds[] = $row->course_id;
		}
		return $courseIds;
	}

	public function buyCourse($userId, $courseId) {
		$data = array(
			'user_id' => $userId,
			'course_id' => $courseId,
		);
		if ($this->checkIfUserHasBoughtCourse($userId, $courseId)) {
			return;
		}
		return $this->tableGateway->insert($data);
	}

	public function emptyCourse($courseId) {
		$this->tableGateway->delete(array('course_id' => $courseId));
	}

	public function checkIfUserHasBoughtCourse($userId, $courseId) {
		$resultSet = $this->tableGateway->select(array('user_id' => $userId, 'course_id' => $courseId));

		if (!empty($resultSet)) {
			return true;
		}
		return false;
	}
}