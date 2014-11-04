<?php

namespace EksamiKeskkond\Model;

use Zend\Db\TableGateway\TableGateway;

class UserTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getUser($id) {
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveUser(User $user) {
		$data = array(
			'role_id' => $user->role_id,
			'firstname' => $user->firstname,
			'lastname' => $user->lastname,
			'email' => $user->email,
			'password' => $user->password,
			'status' => $user->status,
			'registration_date' => $user->registration_date,
		);
		if ($user->id == 0) {
			$this->tableGateway->insert($data);
		}
		else {
			if ($this->getUser($user->id)) {
				$this->tableGateway->update($data, array('id' => $user->id));
			}
			else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function getUserByEmail($email) {
		$rowset = $this->tableGateway->select(array('email' => $email));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $email");
		}
		return $row;
	}

	public function getAllTeachersForSelect() {
		$rowset = $this->tableGateway->select(array('role_id' => 2));
		foreach ($rowset as $row) {
			$result[$row->id] = $rowset->current();
		}
		
		return $result;
	}

	public function deleteUser($id) {
		$this->tableGateway->delete(array('id' => $id));
	}

	public function getUsersByIds(array $ids) {
		$data = array();

		foreach ($ids as $id) {
			$rowset = $this->tableGateway->select(array('id' => $id));
			$data[] = $rowset->current();
		}
		return $data;
	}
}