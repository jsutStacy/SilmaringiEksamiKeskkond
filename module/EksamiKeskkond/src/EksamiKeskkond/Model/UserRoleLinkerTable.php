<?php

namespace EksamiKeskkond\Model;

use Zend\Db\TableGateway\TableGateway;

class UserRoleLinkerTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}
}