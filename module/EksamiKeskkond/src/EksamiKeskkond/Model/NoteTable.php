<?php

namespace EksamiKeskkond\Model;

use Zend\Db\TableGateway\TableGateway;

class NoteTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getNote($id) {
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveNote(Note $note) {
		$data = array(
			'user_id' => $note->user_id,
			'lesson_id' => $note->lesson_id,
			'content' => $note->content,
		);
		if ($note->id == 0) {
			$this->tableGateway->insert($data);
			return $this->tableGateway->lastInsertValue;
		}
		else {
			if ($this->getNote($note->id)) {
				$this->tableGateway->update($data, array('id' => $note->id));
			}
			else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function getNotesByIds(array $ids) {
		$data = array();

		foreach ($ids as $id) {
			$rowset = $this->tableGateway->select(array('id' => $id));
			$data[] = $rowset->current();
		}
		return $data;
	}

	public function deleteNote($id) {
		$this->tableGateway->delete(array('id' => $id));
	}

	public function getNotesByLessonId($lessonId) {
		$result = array();
		$rowset = $this->tableGateway->select(array('lesson_id' => $lessonId));
	
		foreach ($rowset as $row) {
			$result[$row->id] = $rowset->current();
		}
		return $result;
	}
	
	public function getNotesByUserId($userId) {
		$result = array();
		$rowset = $this->tableGateway->select(array('user_id' => $userId));
	
		foreach ($rowset as $row) {
			$result[$row->id] = $rowset->current();
		}
		return $result;
	}

}