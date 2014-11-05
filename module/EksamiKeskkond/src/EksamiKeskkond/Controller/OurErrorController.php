<?php

namespace EksamiKeskkond\Controller;

use Zend\View\Model\ViewModel;

use Zend\Mvc\Controller\AbstractActionController;

class OurErrorController extends AbstractActionController {

	public function indexAction() {
		return new ViewModel();
	}

	public function noPermissionAction() {
		return new ViewModel();
	}
}