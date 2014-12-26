<?php
namespace AmaUsers\Controller;


use AmaUsers\Entity\UserSchool;
use AmaUsers\Form\SettingsFilter;
use AmaUsers\Form\SettingsForm;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity;
use Zend\Config\Reader\Json;
use Zend\Filter\File\RenameUpload;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AdminController extends AbstractActionController
{
    protected $schoolRole = 'school';
    protected $defaultRole = 'v_student';

    /**
     * Main config
     * @var $config
     */
    protected $config;

    /**
     * Entity Manager
     * @var $em
     */
    protected $em;

    public function indexAction()
    {
        return new ViewModel();
    }

    public function pageAction()
    {
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

        $results = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findUsersPagination($args);
        $total = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->countUsers();
        $resultsArray = array(
            'sEcho' => microtime(),
            'iTotalRecord' => $total,
            'iTotalDisplayRecords' => $total
        );
        foreach ($results as $row) {
            $row1 = array(
                $row['id'],
                $row['email'],
                $row['first_name'],
                $row['lastname'],
                $row['roleId'],
                $row['state']==1?$translator->translate("Active"):$translator->translate("Not Active"),
                '<input type="checkbox" name="users[]" value="' . $row['id'] . '">',
                '<a href="' . $this->url()->fromRoute('users', array('action' => 'edit', 'id' => $row['id'])) . '">' . $translator->translate("Edit") . '</a>'
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

    public function editAction()
    {
        if ( $this->params('id') == 0 ) {
            return $this->redirect()->toRoute('error');
        }

        $user = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->find($this->params('id'));
        if ( !$user ) {
            return $this->redirect()->toRoute('error');
        }
        $form = new SettingsForm();

        $roles  =  array(
            'v_student' => 'v_student',
            'v_teacher' => 'v_teacher',
            'admin' => 'admin'
        );
        if ( $user->hasRole('school') ) {
            $roles['school'] = 'school';
        }
        if ( $user->hasRole('k_teacher') ) {
            $roles['k_teacher'] = 'k_teacher';
        }
        if ( $user->hasRole('k_student') ) {
            $roles['k_student'] = 'k_student';
        }

        $form->get('role')->setValueOptions($roles);
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');
        $this->getConfig();

        if ($request->isPost()) {

            $imageExists = $user->getImage();

            $form->setHydrator(new DoctrineEntity($this->getEntityManager(), 'AmaUsers\Entity\User'));
            $form->bind($user);

            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setInputFilter(new SettingsFilter($this->getServiceLocator()));
            $form->setData($post);
            $form->getInputFilter()->get('role')->setRequired(true);

            $files = $request->getFiles('image');
            if (!empty($files['name'])) {
                $form->getInputFilter()->get('image')->setRequired(true);
            }

            if ($form->get('newPassword')->getValue() || $form->get('newPasswordConfirm')->getValue()) {
                $form->getInputFilter()->get('newPassword')->setRequired(true);
                $form->getInputFilter()->get('newPasswordConfirm')->setRequired(true);
            }

            if ($form->isValid()) {

                if ($form->get('newPassword')->getValue()) {
                    $form->getData()->setPassword($this->encryptPassword(
                        $this->config['static_salt'],
                        $form->getInputFilter()->get('newPassword')->getValue(),
                        $user->getDynamicSalt()
                    ));
                }

                $files = $request->getFiles();
                if (!empty($files['image']['name'])) {
                    $filter = new RenameUpload($this->config['profile_image_dir']);
                    $filter->setRandomize(true);
                    $filter->setOverwrite(true);
                    $filter->setUseUploadName(true);
                    $image = $filter->filter($files['image']);
                    $fileName = $this->extractFilename($image['tmp_name']);
                    if (!empty($fileName)) {
                        $form->getData()->setImage($fileName);
                    }
                }

                //image cant be array
                if ( is_array($form->getData()->getImage()) )
                    $form->getData()->setImage($imageExists);

                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();

                //update user row
                $role = $this->getEntityManager()->getRepository('AmaUsers\Entity\Role')->findOneBy(array('roleId' => $post['role']));
                if ( !$role ) {
                    $role = $this->getEntityManager()->getRepository('AmaUsers\Entity\Role')->findOneBy(array('roleId' => 'v_student'));
                    $user->getRoles()->removeElement($user->getSingleRole());
                    $user->addRole($role);
                    $this->getEntityManager()->flush();
                }
                else {
                    $user->getRoles()->removeElement($user->getSingleRole());
                    $user->addRole($role);
                    $this->getEntityManager()->flush();
                }

                $this->clearCache();

                $this->flashMessenger()->addMessage($translator->translate('Successfully updated!'));
                return $this->redirect()->toRoute('users', array('action' => 'edit', 'id' => $this->params('id')));
            }

        }

        $viewModel =  new ViewModel(array(
            'form' => $form,
            'user' => $user,
            'messages' => $this->formatMessage()->doFormat($form->getMessages()),
            'successMessages' => $this->flashMessenger()->getMessages(),
            'errorMessages' => $this->flashMessenger()->getErrorMessages(),
            'imagePath' => $this->config['profile_image_dir'],
        ));

        return $viewModel->setTemplate('ama-users/admin/edit');
    }

    public function addSchoolsAction()
    {
        $request = $this->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ( $this->params('id') == 0 ) {
            return $this->redirect()->toRoute('error');
        }

        if ($request->isPost()) {

            $user = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->find($this->params('id'));
            if ( !$user ) {
                return $this->redirect()->toRoute('error');
            }

            $post = $request->getPost();

            if ( !is_array($post->get('schools')) ) {
                return $this->redirect()->toRoute('error');
            }

            foreach ( $post->get('schools') as $schoolId ) {
                $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->find($schoolId);
                if (!$school) continue;

                $userSchool = $this->getEntityManager()->getRepository('AmaUsers\Entity\UserSchool')->findOneBy(array('user' => $user, 'school' => $school));
                if ($userSchool) continue;

                $userSchool = new UserSchool();
                $userSchool->setSchool($school);
                $userSchool->setUser($user);
                $user->addSchool($userSchool);

                //update user role
                $user->getRoles()->removeElement($user->getSingleRole());
                $this->getEntityManager()->flush();
                $role = $this->getEntityManager()->getRepository('AmaUsers\Entity\Role')->findOneBy(array('roleId' => $this->schoolRole));
                $user->addRole($role);

            }
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
            $this->clearCache($user->getId());
        }

        return new JsonModel(array(
            'success' => true
        ));
    }

    public function removeSchoolAction()
    {
        if ( $this->params('id') == 0 || $this->params('rid') == 0 ) {
            return $this->redirect()->toRoute('error');
        }

        $userSchool = $this->getEntityManager()->getRepository('AmaUsers\Entity\UserSchool')->findOneBy(array('user' => $this->params('id'), 'school' => $this->params('rid')));
        if(!$userSchool) {
            return $this->redirect()->toRoute('error');
        }

        $user = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->find($this->params('id'));
        if ( !$user ) {
            return $this->redirect()->toRoute('error');
        }

        $user->getSchools()->removeElement($userSchool);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
        $this->clearCache($user->getId());

        $userSchoolCount = $this->getEntityManager()->getRepository('AmaUsers\Entity\UserSchool')->findBy(array('user' => $this->params('id')));
        $userSchoolCount = count($userSchoolCount);
        if ( $userSchoolCount == 0 ) {
            //update user role
            $role = $this->getEntityManager()->getRepository('AmaUsers\Entity\Role')->findOneBy(array('roleId' => $this->defaultRole));
            $user->getRoles()->removeElement($user->getSingleRole());
            $user->addRole($role);
            $this->getEntityManager()->flush();
        }

        return $this->redirect()->toRoute('users', array('action' => 'edit', 'id' => $this->params('id')));

    }

    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

    public function getConfig()
    {
        if (!$this->config) {
            return $this->config = $this->getServiceLocator()->get('config');
        }
        return $this->config;
    }

    public function encryptPassword($staticSalt, $password, $dynamicSalt)
    {
        $bcrypt = new Bcrypt();
        return $bcrypt->create($staticSalt . $password . $dynamicSalt);
    }

    public function clearCache($userId = '')
    {
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsers');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsersPaginate');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsersCount');
        if ( $userId ) {
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsersSchools'. $userId);
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSchoolsPaginate'. $userId);
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSchoolsCount'. $userId);
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('UserRoles' . $userId);
        }
    }

    public function extractFilename($fullFilePath)
    {
        $parts = explode("/", $fullFilePath);
        if (!is_array($parts)) return '';
        return end($parts);
    }

}