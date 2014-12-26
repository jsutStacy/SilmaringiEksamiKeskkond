<?php

namespace Eksamikool\Model;

use Zend\Db\TableGateway\TableGateway;

class SubjectTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getSubject($id) {
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveSubject(Subject $subject) {
		$data = array(
			'course_id' => $subject->course_id,
			'name' => $subject->name,
			'description' => $subject->description,
		);
		if ($subject->id == 0) {
			$this->tableGateway->insert($data);
			return $this->tableGateway->lastInsertValue;
		}
		else {
			if ($this->getSubject($subject->id)) {
				$this->tableGateway->update($data, array('id' => $subject->id));
			}
			else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function getSubjectsByIds(array $ids) {
		$data = array();

		foreach ($ids as $id) {
			$rowset = $this->tableGateway->select(array('id' => $id));
			$data[] = $rowset->current();
		}
		return $data;
	}

	public function deleteSubject($id) {
		$this->tableGateway->delete(array('id' => $id));
	}

	public function getSubjectsByCourseId($courseId) {
		$result = array();
		$rowset = $this->tableGateway->select(array('course_id' => $courseId));

		foreach ($rowset as $row) {
			$result[$row->id] = $rowset->current();
		}
		return $result;
	}
}