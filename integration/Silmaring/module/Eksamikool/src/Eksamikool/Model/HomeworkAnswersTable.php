<?php

namespace Eksamikool\Model;

use Zend\Db\TableGateway\TableGateway;

class HomeworkAnswersTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getHomeworkAnswer($id) {
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveHomeworkAnswer(HomeworkAnswers $homeworkAnswer) {
		$data = array(
			'homework_id' => $homeworkAnswer->homework_id,
			'user_id' => $homeworkAnswer->user_id,
			'url' => $homeworkAnswer->url,
		);
		if ($homeworkAnswer->id == 0) {
			$this->tableGateway->insert($data);
		}
		else {
			if ($this->getHomeworkAnswer($homeworkAnswer->id)) {
				$this->tableGateway->update($data, array('id' => $homeworkAnswer->id));
			}
			else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function getHomeworkAnswerByUserIdAndHomeworkId($userId, $homeworkId) {
		$rowset = $this->tableGateway->select(array('user_id' => $userId, 'homework_id' => $homeworkId));

		return $rowset->current();
	}

	public function getHomeworkAnswersByHomeworkId($homeworkId) {
		return $this->tableGateway->select(array('homework_id' => $homeworkId));
	}

	public function updateFeedback($homeworkId, $userId, $feedback) {
		return $this->tableGateway->update(array('feedback' => $feedback), array('homework_id' => $homeworkId, 'user_id' => $userId));
	}

	public function deleteFeedback($id) {
		return $this->tableGateway->update(array('feedback' => NULL), array('id' => $id));
	}
}