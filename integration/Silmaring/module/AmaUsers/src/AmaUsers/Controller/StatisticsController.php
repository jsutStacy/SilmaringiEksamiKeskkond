<?php
namespace AmaUsers\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class StatisticsController extends AbstractActionController
{
    /**
     * Entity Manager
     * @var $em
     */
    protected $em;

    protected $allowedTypes = array('daily', 'weekly', 'monthly');

    public function indexAction()
    {
        $daysInMonth = $this->getDaysInMonth(date('Y-m'));
        $user = $this->identity();
        $hours = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->getDayilyHoursOnline(date('Y-m-d'), $daysInMonth, $user);

        $classes = array();
        $template = 'ama-users/statistics/index';
        if($user->hasRole('k_teacher')) {
            $template = 'ama-users/statistics/index-teacher';
            $classes = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findUsersClasses($user);
        }
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
                'daysInMonth' => $daysInMonth,
                'hours' => $hours,
                'classes' => $classes
            ))
            ->setTemplate($template);

        return $viewModel;
    }

    public function typeAction()
    {
        $request = $this->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }
        if(!$request->isPost()) {
            return $this->redirect()->toRoute('error');
        }
        $post = $request->getPost()->toArray();
        if(empty($post['type']) || !in_array($post['type'], $this->allowedTypes)) {
            return $this->redirect()->toRoute('error');
        }
        $user = $this->identity();
        if(empty($post['class_id'])) $post['class_id'] = '';

        if($post['type'] == 'daily') {
            $daysInMonth = $this->getDaysInMonth(date('Y-m'));
            if(!empty($post['class_id'])) {
                $hours = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->getDayilyHoursOnlineByClass(date('Y-m-d'), $daysInMonth, $user, $post['class_id']);
            }
            else {
                $hours = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->getDayilyHoursOnline(date('Y-m-d'), $daysInMonth, $user);
            }
        }
        else if($post['type'] == 'weekly') {
            $dateTo = date('Y-m-d');
            $dateFrom  = date('Y-m-d', strtotime($dateTo .' -3 months'));
            $daysInMonth = $this->getWeeksByMonth($dateFrom, $dateTo);
            if(!empty($post['class_id'])) {
                $hours = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->getWeeklyHoursOnlineByClass($dateFrom, $dateTo, $daysInMonth, $user, $post['class_id']);
            }
            else {
                $hours = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->getWeeklyHoursOnline($dateFrom, $dateTo, $daysInMonth, $user);
            }
        }
        else {
            $daysInMonth = $this->getMonths();
            if(!empty($post['class_id'])) {
                $hours = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->getMonthlyHoursOnlineByClass(date('Y-m-d'), $daysInMonth, $user, $post['class_id']);
            }
            else {
                $hours = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->getMonthlyHoursOnline(date('Y-m-d'), $daysInMonth, $user);
            }
        }

        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'daysInMonth' => $daysInMonth,
                'hours' => $hours,
                'type' => $post['type'],
                'class_id' => $post['class_id']
            ))
            ->setTemplate("ama-users/statistics/type-". $post['type']);

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);


        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput
        ));
    }

    public function tabAction()
    {
        $request = $this->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }
        if(!$request->isPost()) {
            return $this->redirect()->toRoute('error');
        }

        $post = $request->getPost()->toArray();
        $user = $this->identity();

        if(empty($post['class_id'])) {
            return $this->redirect()->toRoute('error');
        }

        $daysInMonth = $this->getDaysInMonth(date('Y-m'));
        $hours = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->getDayilyHoursOnlineByClass(date('Y-m-d'), $daysInMonth, $user, $post['class_id']);

        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'daysInMonth' => $daysInMonth,
                'hours' => $hours,
                'class_id' => $post['class_id']
            ))
            ->setTemplate("ama-users/statistics/index-teacher-class");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);


        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput
        ));
    }


    public function getMonths()
    {
        $months = array();
        for ($m=1; $m<=12; $m++) {
            $months[] = array(
                'name' => date('F', mktime(0,0,0,$m, 1, date('Y'))),
                'nr' => ($m<10?'0':'') . $m
            );
        }
        return $months;
    }

    public function getDaysInMonth($month)
    {
        return date('t', strtotime($month));
    }

    public function getWeeksByMonth($dateFrom, $dateTo)
    {

        $p = new \DatePeriod(
            new \DateTime($dateFrom),
            new \DateInterval('P1W'),
            new \DateTime($dateTo)
        );
        foreach($p as $w) {
            $weeks[] = $w->format('W');
        }
        return $weeks;
    }

    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }


}