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

		foreach ($resultSet as $key => $row) {
			$userIds[$key]['id'] = $row->user_id;
			$userIds[$key]['status'] = $row->status;
		}
		return $userIds;
	}

	public function getCourseByUserId($userId) {
		$rowset = $this->tableGateway->select(array('user_id' => $userId, 'status' => true));

		return $rowset->current();
	}

	public function getAllCoursesByUserId($userId) {
		$courseIds = array();
		$resultSet = $this->tableGateway->select(array('user_id' => $userId, 'status' => true));

		foreach ($resultSet as $row) {
			$courseIds[] = $row->course_id;
		}
		return $courseIds;
	}

	public function buyCourse($userId, $courseId, $status) {
		$data = array(
			'user_id' => $userId,
			'course_id' => $courseId,
			'status' => $status,
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
		$resultSet = $this->tableGateway->select(array('user_id' => $userId, 'course_id' => $courseId, 'status' => true));

		if ($resultSet->current()) {
			return true;
		}
		return false;
	}

	public function changeStatus($userId, $courseId, $status) {
		return $this->tableGateway->update(array('status' => $status), array('user_id' => $userId, 'course_id' => $courseId));
	}
}