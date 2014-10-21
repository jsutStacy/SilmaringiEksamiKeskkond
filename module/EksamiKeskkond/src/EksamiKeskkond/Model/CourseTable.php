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
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveCourse(Course $course) {
		$data = array(
			'teacher_id' => !empty($course->teacher_id) ? $course->teacher_id : NULL,
			'name' => $course->name,
			'description' => $course->description,
			'price' => $course->price,
			'published' => $course->published,
		);
		if ($course->id == 0) {
			$this->tableGateway->insert($data);
		}
		else {
			if ($this->getCourse($course->id)) {
				$this->tableGateway->update($data, array('id' => $course->id));
			}
			else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function changeCourseVisibility($id) {
		$course = $this->getCourse($id);
		$visibility = $course->published == true ? 0 : 1;

		$this->tableGateway->update(
			array('published' => $visibility),
			array('id' => $id)
		);
	}

	public function deleteCourse($id) {
		$this->tableGateway->delete(array('id' => $id));
	}
}