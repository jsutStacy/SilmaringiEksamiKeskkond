<?php

namespace EksamiKeskkond\Model;

use Zend\Db\TableGateway\TableGateway;

class HomeworkTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getHomework($id) {
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();
	
		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveHomework(Homework $homework) {
		$data = array(
			'subsubject_id' => $homework->subsubject_id,
			'user_id' => $homework->user_id,
			'description' => $homework->description,
			'url' => $homework->url,
		);
		if ($homework->id == 0) {
			$this->tableGateway->insert($data);

			return $this->tableGateway->lastInsertValue;
		}
		else {
			if ($this->getHomework($homework->id)) {
				$this->tableGateway->update($data, array('id' => $homework->id));
			}
			else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function getHomeworkBySubsubjectId($subsubjectId) {
		$result = array();
		$rowset = $this->tableGateway->select(array('subsubject_id' => $subsubjectId));

		foreach ($rowset as $row) {
			$result[$row->id] = $rowset->current();
		}
		return $result;
	}

	public function deleteHomeworkFile($id) {
		$this->tableGateway->update(array('url' => null), array('id' => $id));
	}
}