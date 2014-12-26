<?php

namespace AmaMaterials\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class WorksheetController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }


}

