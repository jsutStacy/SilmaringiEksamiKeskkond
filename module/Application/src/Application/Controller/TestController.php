<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Just a Test.
 *
 * @author Andres
 */
class TestController extends AbstractActionController {

	protected $testTable;

	public function indexAction() {
		return new ViewModel(array(
			'testData' => $this->getTestTable()->fetchAll(),
		));
	}

	public function addAction() {
		return new ViewModel();
	}

	public function getTesttable() {
		if (!$this->testTable) {
			$sm = $this->getServiceLocator();
			$this->testTable = $sm->get('Application\Model\TestTable');
		}
		return $this->testTable;
	}
}