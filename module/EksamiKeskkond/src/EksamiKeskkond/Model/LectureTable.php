<?php

namespace EksamiKeskkond\Model;

use Zend\Db\TableGateway\TableGateway;

class LectureTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getLecture($id) {
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveLecture(Lecture $lecture) {
		$data = array(
			'course_id' => $lecture->course_id,
			'subject_id' => $lecture->subject_id,
			'name' => $lecture->name,
			'content' => $lecture->content,
		);
		if ($lecture->id == 0) {
			$this->tableGateway->insert($data);
		}
		else {
			if ($this->getSubject($lecture->id)) {
				$this->tableGateway->update($data, array('id' => $subject->id));
			}
			else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function getLecturesByIds(array $ids) {
		$data = array();

		foreach ($ids as $id) {
			$rowset = $this->tableGateway->select(array('id' => $id));
			$data[] = $rowset->current();
		}
		return $data;
	}

	public function deleteLecture($id) {
		$this->tableGateway->delete(array('id' => $id));
	}

	public function getLecturesBySubjectId($subjectId) {
		$result = array();
		$rowset = $this->tableGateway->select(array('subject_id' => $subjectId));
	
		foreach ($rowset as $row) {
			$result[$row->id] = $rowset->current();
		}
		return $result;
	}

}