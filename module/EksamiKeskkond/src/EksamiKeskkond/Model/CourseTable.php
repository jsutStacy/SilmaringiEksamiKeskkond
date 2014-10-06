<?php

namespace EksamiKeskkond\Model;

use Zend\Db\TableGateway\TableGateway;

class CourseTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getCourse($id) {
		$id = (int) $id;

		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveCourse(Course $course) {
		$data = array(
			'name' => $course->name,
			'price' => $course->price,
			'published' => $course->published,
		);

		$id = (int) $course->id;

		if ($id == 0) {
			$this->tableGateway->insert($data);
		}
		else {
			if ($this->getCourse($id)) {
				$this->tableGateway->update($data, array('id' => $id));
			}
			else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function deleteCourse($id) {
		$this->tableGateway->delete(array('id' => $id));
	}

}