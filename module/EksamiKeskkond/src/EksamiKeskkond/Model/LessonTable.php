<?php

namespace EksamiKeskkond\Model;

use Zend\Db\TableGateway\TableGateway;

class LessonTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getLesson($id) {
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveLesson(Lesson $lesson) {
		$data = array(
			'subsubject_id' => $lesson->subsubject_id,
			'name' => $lesson->name,
			'content' => $lesson->content,
			'type' => $lesson->type,
		);
		if ($lesson->id == 0) {
			$this->tableGateway->insert($data);
			return $this->tableGateway->lastInsertValue;
		}
		else {
			if ($this->getLesson($lesson->id)) {
				$this->tableGateway->update($data, array('id' => $lesson->id));
			}
			else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function getLessonsByIds(array $ids) {
		$data = array();

		foreach ($ids as $id) {
			$rowset = $this->tableGateway->select(array('id' => $id));
			$data[] = $rowset->current();
		}
		return $data;
	}

	public function deleteLesson($id) {
		$this->tableGateway->delete(array('id' => $id));
	}

	public function getLessonsBySubsubjectId($subsubjectId) {
		$result = array();
		$rowset = $this->tableGateway->select(array('subsubject_id' => $subsubjectId));
	
		foreach ($rowset as $row) {
			$result[$row->id] = $rowset->current();
		}
		return $result;
	}
}