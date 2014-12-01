<?php

namespace EksamiKeskkond\Model;

use Zend\Db\TableGateway\TableGateway;

class LessonFilesTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getLessonFiles($id) {
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveLessonFiles(LessonFiles $lessonFile) {
		$data = array(
			'lesson_id' => $lessonFile->lesson_id,
			'user_id' => $lessonFile->user_id,
			'url' => $lessonFile->url,
		);
		if ($lessonFile->id == 0) {
			$this->tableGateway->insert($data);
		}
		else {
			if ($this->getLessonFiles($lessonFile->id)) {
				$this->tableGateway->update($data, array('id' => $lessonFile->id));
			}
			else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function getLessonFilesByIds(array $ids) {
		$data = array();

		foreach ($ids as $id) {
			$rowset = $this->tableGateway->select(array('id' => $id));
			$data[] = $rowset->current();
		}
		return $data;
	}

	public function deleteLessonFile($id) {
		$this->tableGateway->delete(array('id' => $id));
	}

	public function getLessonFilesByLessonId($lessonId) {
		$result = array();
		$rowset = $this->tableGateway->select(array('lesson_id' => $lessonId));

		foreach ($rowset as $row) {
			$result[$row->id] = $rowset->current();
		}
		return $result;
	}

}