<?php
namespace AmaSchools\Controller;

use AmaSchools\Entity\School;
use AmaSchools\Form\AddFilter;
use AmaSchools\Form\AddForm;
use AmaSchools\Form\EditFilter;
use AmaSchools\Form\EditForm;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class IndexController extends  AbstractActionController
{

    /**
     * Entity Manager
     * @var $em
     */
    protected $em;

    public function indexAction()
    {
        return new ViewModel(array(
            'successMessages' => $this->flashMessenger()->getMessages()
        ));
    }

    public function chooseAction()
    {

        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setTemplate("ama-schools/index/choose")
            ->setVariables(array(
                'user' => $this->params('id')
            ));

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
                'success' => true,
                'html' => $htmlOutput,
                'title' => $translator->translate("Choose schools")
            ));
    }

    public function pageAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        $translator = $this->getServiceLocator()->get('translator');

        $limit = (int)$this->params()->fromQuery('iDisplayLength', null);
        $start = (int)$this->params()->fromQuery('iDisplayStart', null);
        $orderBy = (int)$this->params()->fromQuery('iSortCol_0', null);
        $order = $this->params()->fromQuery('sSortDir_0', null);
        $search = $this->params()->fromQuery('sSearch', null);

        $orderAttr = $this->params()->fromQuery('order');
        if (!empty($orderAttr) && $orderBy != 0) {
            $order = 'DESC';
            $orderBy = $orderAttr;
        }

        $args = array(
            'start' => $start,
            'limit' => $limit,
            'orderBy' => $orderBy,
            'order' => $order,
            'search' => $search
        );

        $results = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->findSchoolsPagination($args);
        $total = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->countSchools();
        $resultsArray = array(
            'sEcho' => microtime(),
            'iTotalRecord' => $total,
            'iTotalDisplayRecords' => $total
        );
        //when we have user id
        $userSchools = array();
        if ( $this->params('id')>0 ) {
            $userSchools = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findUserSchoolsAsId($this->params('id'));
        }

        foreach ($results as $row) {
            $checked = '';
            if ( in_array($row['id'], $userSchools)) $checked = "checked disabled";

            $row1 = array(
                $row['id'],
                $row['name'],
                $row['status']==1?$translator->translate("Public"):$translator->translate("Private"),
                '<input type="checkbox" name="schools[]" value="' . $row['id'] . '" '.$checked.'>',
                '<a href="' . $this->url()->fromRoute('schools', array('action' => 'edit', 'id' => $row['id'])) . '">' . $translator->translate("Edit") . '</a>'
            );
            $resultsArray['aaData'][] = $row1;
        }

        if (empty($results)) {
            $resultsArray['aaData'] = array();
        }

        return new JsonModel(
            $resultsArray
        );
    }

    public function addAction()
    {
        $form = new AddForm;
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');


        if ($request->isPost()) {
            $school = new School();
            $form->setHydrator(new DoctrineEntity($this->getEntityManager(), 'AmaSchools\Entity\School'));
            $form->bind($school);

            $post = $request->getPost();
            $form->setInputFilter(new AddFilter($this->getServiceLocator()));
            $form->setData($post);

            if ( $form->isValid() ) {
                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();
                $this->clearCache();

                $this->flashMessenger()->addMessage($translator->translate('Successfully added!'));
                return $this->redirect()->toRoute('schools');
            }

          }

        return new ViewModel(array(
            'form' => $form,
            'messages' => $this->formatMessage()->doFormat($form->getMessages()),
            'errorMessages' => $this->flashMessenger()->getErrorMessages(),

        ));
    }

    public function editAction()
    {
        if ( $this->params('id') == 0 ) {
            return $this->redirect()->toRoute('error');
        }

        $form = new EditForm;
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');
        $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->find($this->params('id'));

        if ( !$school ) {
            return $this->redirect()->toRoute('error');
        }

        if ($request->isPost()) {

            $form->setHydrator(new DoctrineEntity($this->getEntityManager(), 'AmaSchools\Entity\School'));
            $form->bind($school);

            $post = $request->getPost();
            $form->setInputFilter(new EditFilter($this->getServiceLocator()));
            $form->setData($post);

            if ( $form->isValid() ) {
                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();
                $this->clearCache();
                $this->clearUsersCache($form->getData()->getId());

                $this->flashMessenger()->addMessage($translator->translate('Successfully updated!'));
                return $this->redirect()->toRoute('schools', array('action' => 'edit', 'id' => $this->params('id')));
            }

        }


        return new ViewModel(array(
            'form' => $form,
            'messages' => $this->formatMessage()->doFormat($form->getMessages()),
            'errorMessages' => $this->flashMessenger()->getErrorMessages(),
            'successMessages' => $this->flashMessenger()->getMessages(),
            'data' => $school
        ));
    }

    public function deleteAction()
    {
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');

        if ($request->isPost()) {
            $schools = $request->getPost()->toArray();

            if ( !isset($schools['schools']) ) $schools['schools'] = array();

                foreach ($schools['schools'] as $school) {
                    if (empty($school)) continue;
                        $schoolData = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->find($school);
                        $this->getEntityManager()->remove($schoolData);
                        $this->getEntityManager()->flush();
                        $this->clearCache();
                        $this->clearUsersCache($schoolData->getId());
                        $this->flashMessenger()->addMessage($translator->translate('Successfully deleted!'));
                }
        }
        return $this->redirect()->toRoute('schools');
    }

    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

    public function clearCache()
    {
        $user = $this->identity();
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSchools');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSchoolsPaginate');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSchoolsCount');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsersClasses'. $user->getId());
    }

    public function clearUsersCache($school)
    {
        $schoolUsers = $this->getEntityManager()->getRepository('AmaUsers\Entity\UserSchool')->findBy(array('school' => $school));
        foreach($schoolUsers as $school) {
            $user = $school->getUser()->getId();
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsersSchools'. $user);
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSchoolsPaginate'. $user);
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSchoolsCount'. $user);
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsersClasses'. $user);
        }
    }
}