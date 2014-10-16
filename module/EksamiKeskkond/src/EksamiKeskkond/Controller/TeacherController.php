<?php

namespace EksamiKeskkond\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TeacherController extends AbstractActionController {

	public function indexAction() {
		return new ViewModel();
	}
}