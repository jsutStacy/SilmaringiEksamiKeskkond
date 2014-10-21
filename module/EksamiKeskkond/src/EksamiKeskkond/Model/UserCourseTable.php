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

	public function getCourseParticipants($course_id) {
		$userIds = array();
		$resultSet = $this->tableGateway->select(array('course_id' => $course_id));

		foreach ($resultSet as $row) {
			$userIds[] = $row->user_id;
		}
		return $userIds;
	}

	public function emptyCourse($course_id) {
		$this->tableGateway->delete(array('course_id' => $course_id));
	}
}