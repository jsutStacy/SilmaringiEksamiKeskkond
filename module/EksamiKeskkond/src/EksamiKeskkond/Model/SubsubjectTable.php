<?php

namespace EksamiKeskkond\Model;

use Zend\Db\TableGateway\TableGateway;

class SubsubjectTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getSubsubject($id) {
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveSubsubject(Subsubject $subsubject) {
		$data = array(
			'subject_id' => $subsubject->subject_id,
			'name' => $subsubject->name,
		);
		if ($subsubject->id == 0) {
			$this->tableGateway->insert($data);
			return $this->tableGateway->lastInsertValue;
		}
		else {
			if ($this->getSubsubject($subsubject->id)) {
				$this->tableGateway->update($data, array('id' => $subsubject->id));
			}
			else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function getSubsubjectsByIds(array $ids) {
		$data = array();
	
		foreach ($ids as $id) {
			$rowset = $this->tableGateway->select(array('id' => $id));
			$data[] = $rowset->current();
		}
		return $data;
	}

	public function deleteSubsubject($id) {
		$this->tableGateway->delete(array('id' => $id));
	}
	
	public function getSubsubjectsBySubjectId($subjectId) {
		$result = array();
		$rowset = $this->tableGateway->select(array('subject_id' => $subjectId));

		foreach ($rowset as $row) {
			$result[$row->id] = $rowset->current();
		}
		return $result;
	}
}