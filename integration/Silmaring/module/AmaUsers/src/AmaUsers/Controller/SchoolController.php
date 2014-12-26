<?php
namespace AmaUsers\Controller;


use AmaSchools\Entity\SchoolClass;
use AmaSchools\Entity\SchoolTeacher;
use AmaUsers\Entity\Student;
use AmaUsers\Entity\StudentClass;
use AmaUsers\Entity\Teacher;
use AmaUsers\Entity\TeacherClass;
use AmaUsers\Entity\UserSchool;
use AmaUsers\Form\AddStudent;
use AmaUsers\Form\AddStudentFilter;
use AmaUsers\Form\AddTeacher;
use AmaUsers\Form\AddTeacherFilter;
use AmaUsers\Form\EditStudent;
use AmaUsers\Form\EditStudentFilter;
use AmaUsers\Form\EditTeacher;
use AmaUsers\Form\EditTeacherFilter;
use AmaUsers\Form\SettingsFilter;
use AmaUsers\Form\SettingsForm;
use BjyAuthorize\Exception\UnAuthorizedException;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity;
use Zend\Config\Reader\Json;
use Zend\Filter\File\RenameUpload;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class SchoolController extends AbstractActionController
{

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
        return new ViewModel(array(
            'successMessages' => $this->flashMessenger()->getMessages()
        ));
    }

    public function pageAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        $translator = $this->getServiceLocator()->get('translator');
        $user = $this->identity();
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

        $results = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->findSchoolsByUserPagination($user, $args);
        $total = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->countSchoolsByUser($user);
        $resultsArray = array(
            'sEcho' => microtime(),
            'iTotalRecord' => $total,
            'iTotalDisplayRecords' => $total
        );

        foreach ($results as $row) {
            $row1 = array(
                $row['name'],
                '<a href="' . $this->url()->fromRoute('mySchools', array('action' => 'manage', 'id' => $row['id'])) . '">' . $translator->translate("Manage") . '</a>'
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

    public function manageAction()
    {
        if ( $this->params('id')  == 0 ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $user = $this->identity();

        $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->getSchoolByIdAndUser($this->params('id'), $user);
        if ( !$school ) {
            throw new UnAuthorizedException('Wrong id');
        }
        $this->getConfig();

        $form = new \AmaSchools\Form\SettingsForm();
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');

        $classes = $school->getClasses();
        $classes = $this->formatClasses($classes);

        if ($request->isPost()) {

            $form->setHydrator(new DoctrineEntity($this->getEntityManager(), 'AmaSchools\Entity\School'));
            $form->bind($school);

            $post = $request->getPost();
            $form->setInputFilter(new \AmaSchools\Form\SettingsFilter($this->getServiceLocator()));
            $form->setData($post);

            if ( $form->isValid() ) {
                //$form->getData()->removeAllClasses();
                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();

                foreach ( $post->get('schoolClasses') as $class ) {
                    if(!in_array($class, $classes)) {
                        $schoolClass = new SchoolClass();
                        $schoolClass->setSchool($school);
                        $schoolClass->setClassName($class);
                        $this->getEntityManager()->persist($schoolClass);
                        $this->getEntityManager()->flush();
                        unset($classes[$class]);
                    }
                    else {
                        unset($classes[$class]);
                    }
                }

                //remove classes that are not chosen
                foreach ( $classes  as $class ) {
                    $classData = $this->getEntityManager()->getRepository('AmaSchools\Entity\SchoolClass')->findOneBy(array('school' => $school, 'className' => $class));
                    if($classData) {
                    $form->getData()->removeClass($classData);
                    $this->getEntityManager()->persist($form->getData());
                    $this->getEntityManager()->flush();
                    }
                }

                $this->clearCache();
                $this->clearUsersCache($form->getData()->getId());
                $this->clearCacheBySchool($form->getData());

                $this->flashMessenger()->addMessage($translator->translate('Successfully updated!'));
                return $this->redirect()->toRoute('mySchools', array('action' => 'manage', 'id' => $this->params('id')));
            }

        }

        $this->layout()->setVariable('school', $school);
        return new ViewModel(array(
            'school' => $school,
            'school_classes' => $this->config['school_classes'],
            'school_classes_letters' => $this->config['school_classes_letters'],
            'messages' => $this->formatMessage()->doFormat($form->getMessages()),
            'errorMessages' => $this->flashMessenger()->getErrorMessages(),
            'successMessages' => $this->flashMessenger()->getMessages(),
            'form' => $form,
            'chosenClasses' => $classes
        ));
    }

    public function manageTeachersAction()
    {
        if ( $this->params('id')  == 0 ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $user = $this->identity();

        $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->getSchoolByIdAndUser($this->params('id'), $user);
        if ( !$school ) {
            throw new UnAuthorizedException('Wrong id');
        }
        $this->layout()->setVariable('school', $school);
        return new ViewModel(array(
            'school' => $school,
            'orderBy' => $this->params()->fromQuery('order'),
            'successMessages' => $this->flashMessenger()->getMessages()
        ));
    }

    public function manageStudentsAction()
    {
        if ( $this->params('id')  == 0 ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $user = $this->identity();

        $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->getSchoolByIdAndUser($this->params('id'), $user);
        if ( !$school ) {
            throw new UnAuthorizedException('Wrong id');
        }
        $this->layout()->setVariable('school', $school);
        return new ViewModel(array(
            'school' => $school,
            'orderBy' => $this->params()->fromQuery('order'),
            'successMessages' => $this->flashMessenger()->getMessages()
        ));
    }

    public function manageTeachersPageAction()
    {

        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ( $this->params('id')  == 0 ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $user = $this->identity();

        $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->getSchoolByIdAndUser($this->params('id'), $user);
        if ( !$school ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $translator = $this->getServiceLocator()->get('translator');
        $user = $this->identity();
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

        $results = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->findTeachersBySchoolPagination($school, $args);
        $total = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->countTeachersBySchool($school);
        $resultsArray = array(
            'sEcho' => microtime(),
            'iTotalRecord' => $total,
            'iTotalDisplayRecords' => $total
        );

        foreach ($results as $row) {
            $row1 = array(
                $row['first_name'],
                $row['lastname'],
                $row['teacher_classes'],
                $row['email'],
                '<input type="checkbox" name="teachers[]" value="'.$row['id'].'" />',
                '<a href="' . $this->url()->fromRoute('mySchools', array('action' => 'edit-teacher', 'id' => $school->getId(), 'tid' => $row['id'])) . '">' . $translator->translate("Edit") . '</a>'
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

    public function manageStudentsPageAction()
    {

        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ( $this->params('id')  == 0 ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $user = $this->identity();

        $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->getSchoolByIdAndUser($this->params('id'), $user);
        if ( !$school ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $translator = $this->getServiceLocator()->get('translator');
        $user = $this->identity();
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

        $results = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->findStudentsBySchoolPagination($school, $args);
        $total = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->countStudentsBySchool($school);
        $resultsArray = array(
            'sEcho' => microtime(),
            'iTotalRecord' => $total,
            'iTotalDisplayRecords' => $total
        );

        foreach ($results as $row) {
            $row1 = array(
                $row['first_name'],
                $row['lastname'],
                $row['student_classes'],
                $row['email'],
                '<input type="checkbox" name="students[]" value="'.$row['id'].'" />',
                '<a href="' . $this->url()->fromRoute('mySchools', array('action' => 'edit-student', 'id' => $school->getId(), 'tid' => $row['id'])) . '">' . $translator->translate("Edit") . '</a>'
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

    public function addTeacherAction()
    {
        if ( $this->params('id')  == 0 ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $user = $this->identity();
        $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->getSchoolByIdAndUser($this->params('id'), $user);
        if ( !$school ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $form = new AddTeacher();
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');

        if ($request->isPost()) {
            $teacher = new Teacher();
            $form->setHydrator(new DoctrineEntity($this->getEntityManager(), 'AmaUsers\Entity\Teacher'));
            $form->bind($teacher);

            $post = $request->getPost();
            $form->setInputFilter(new AddTeacherFilter($this->getServiceLocator()));
            $form->setData($post);

            if ( $form->isValid() ) {

                $form->getData()->setSchool($school);
                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();

                foreach ( $post->get('teacherClasses') as $class ) {
                        $schoolClassData = $this->getEntityManager()->getRepository('AmaSchools\Entity\SchoolClass')->findOneBy(array('school' => $school, 'id' => $class));
                        if ( $schoolClassData ) {
                            $teacherClass = new TeacherClass();
                            $teacherClass->setSchool($school);
                            $teacherClass->setClass($schoolClassData);
                            $teacherClass->setTeacher($form->getData());
                            $this->getEntityManager()->persist($teacherClass);
                            $this->getEntityManager()->flush();
                        }
                }

                $this->clearCache();
                $this->clearUsersCache($school);
                $this->clearCacheBySchool($school);

                $this->flashMessenger()->addMessage($translator->translate('Successfully added!'));
                return $this->redirect()->toRoute('mySchools', array('action' => 'manage-teachers', 'id' => $school->getId()));
            }

        }
        $classesList = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->findClassesBySchool($school);
        $this->layout()->setVariable('school', $school);
        return new ViewModel(array(
            'form' => $form,
            'school' => $school,
            'classes' => $classesList,
            'messages' => $this->formatMessage()->doFormat($form->getMessages()),
            'errorMessages' => $this->flashMessenger()->getErrorMessages(),
        ));
    }

    public function editTeacherAction()
    {
        if ( $this->params('id')  == 0 ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $user = $this->identity();
        $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->getSchoolByIdAndUser($this->params('id'), $user);
        if ( !$school ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $teacher = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->findTeacherBySchoolAndTid($school, $this->params('tid'));
        if ( !$teacher ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $form = new EditTeacher();
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');

        $classes = $teacher->getClasses();
        $classes = $this->formatTeacherClasses($classes);


        if ($request->isPost()) {
            $form->setHydrator(new DoctrineEntity($this->getEntityManager(), 'AmaUsers\Entity\Teacher'));
            $form->bind($teacher);

            $post = $request->getPost();
            $form->setInputFilter(new EditTeacherFilter($this->getServiceLocator()));
            $form->setData($post);

            if ( $form->isValid() ) {
                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();


                foreach ( $post->get('teacherClasses') as $class ) {
                    if(!in_array($class, $classes)) {
                        $schoolClassData = $this->getEntityManager()->getRepository('AmaSchools\Entity\SchoolClass')->findOneBy(array('school' => $school, 'id' => $class));
                        if ( $schoolClassData ) {
                        $teacherClass = new TeacherClass();
                        $teacherClass->setSchool($school);
                        $teacherClass->setClass($schoolClassData);
                        $teacherClass->setTeacher($form->getData());
                        $this->getEntityManager()->persist($teacherClass);
                        $this->getEntityManager()->flush();
                        }
                        unset($classes[$class]);

                    }
                    else {
                        unset($classes[$class]);
                    }
                }

                //remove classes that are not chosen
                foreach ( $classes  as $class ) {
                    $classData = $this->getEntityManager()->getRepository('AmaUsers\Entity\TeacherClass')->findOneBy(array('school' => $school, 'teacher' => $form->getData(), 'class' => $class));
                    if($classData) {
                        $form->getData()->removeClass($classData);
                        $this->getEntityManager()->persist($form->getData());
                        $this->getEntityManager()->flush();
                    }
                }

                $this->clearCache();
                $this->clearUsersCache($school);
                $this->clearCacheBySchool($school);
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsersClasses'. $form->getData()->getFirstTeacherUser()->getUser()->getId());

                $this->flashMessenger()->addMessage($translator->translate('Successfully updated!'));
                return $this->redirect()->toRoute('mySchools', array('action' => 'edit-teacher', 'id' => $school->getId(),'tid' => $form->getData()->getId()));
            }

        }
        $classesList = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->findClassesBySchool($school);
        $this->layout()->setVariable('school', $school);
        return new ViewModel(array(
            'form' => $form,
            'school' => $school,
            'teacher' => $teacher,
            'classes' => $classesList,
            'chosenClasses' => $classes,
            'messages' => $this->formatMessage()->doFormat($form->getMessages()),
            'errorMessages' => $this->flashMessenger()->getErrorMessages(),
            'successMessages' => $this->flashMessenger()->getMessages()
        ));
    }

    public function deleteTeacherAction()
    {
        if ( $this->params('id')  == 0 ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $user = $this->identity();
        $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->getSchoolByIdAndUser($this->params('id'), $user);
        if ( !$school ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');

        if ($request->isPost()) {
            $post = $request->getPost()->toArray();

            if ( !isset($post['teachers']) ) $post['teachers'] = array();

            foreach ($post['teachers'] as $teacher) {
                if (empty($school)) continue;
                $teacherData = $this->getEntityManager()->getRepository('AmaUsers\Entity\Teacher')->findOneBy(array('school' => $school, 'id' => $teacher));
                if ( $teacherData ) {
                $this->getEntityManager()->remove($teacherData);
                $this->getEntityManager()->flush();
                $this->clearCache();
                $this->clearUsersCache($school);
                $this->clearCacheBySchool($school);
                }
            }
            $this->flashMessenger()->addMessage($translator->translate('Successfully deleted!'));
        }
        return $this->redirect()->toRoute('mySchools', array('action' => 'manage-teachers', 'id' => $school->getId()));
    }


    public function addStudentAction()
    {
        if ( $this->params('id')  == 0 ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $user = $this->identity();
        $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->getSchoolByIdAndUser($this->params('id'), $user);
        if ( !$school ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $form = new AddStudent();
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');

        if ($request->isPost()) {
            $student = new Student();
            $form->setHydrator(new DoctrineEntity($this->getEntityManager(), 'AmaUsers\Entity\Student'));
            $form->bind($student);

            $post = $request->getPost();
            $form->setInputFilter(new AddStudentFilter($this->getServiceLocator()));
            $form->setData($post);

            if ( $form->isValid() ) {

                $form->getData()->setSchool($school);
                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();

                foreach ( $post->get('studentClasses') as $class ) {
                    $schoolClassData = $this->getEntityManager()->getRepository('AmaSchools\Entity\SchoolClass')->findOneBy(array('school' => $school, 'id' => $class));
                    if ( $schoolClassData ) {
                        $studentClass = new StudentClass();
                        $studentClass->setSchool($school);
                        $studentClass->setClass($schoolClassData);
                        $studentClass->setStudent($form->getData());
                        $this->getEntityManager()->persist($studentClass);
                        $this->getEntityManager()->flush();
                    }
                }

                $this->clearCache();
                $this->clearUsersCache($school);
                $this->clearCacheBySchool($school);

                $this->flashMessenger()->addMessage($translator->translate('Successfully added!'));
                return $this->redirect()->toRoute('mySchools', array('action' => 'manage-students', 'id' => $school->getId()));
            }

        }
        $classesList = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->findClassesBySchool($school);
        $this->layout()->setVariable('school', $school);
        return new ViewModel(array(
            'form' => $form,
            'school' => $school,
            'classes' => $classesList,
            'messages' => $this->formatMessage()->doFormat($form->getMessages()),
            'errorMessages' => $this->flashMessenger()->getErrorMessages(),
        ));
    }


    public function deleteStudentAction()
    {
        if ( $this->params('id')  == 0 ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $user = $this->identity();
        $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->getSchoolByIdAndUser($this->params('id'), $user);
        if ( !$school ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');

        if ($request->isPost()) {
            $post = $request->getPost()->toArray();

            if ( !isset($post['students']) ) $post['teachers'] = array();

            foreach ($post['students'] as $student) {
                if (empty($school)) continue;
                $studentData = $this->getEntityManager()->getRepository('AmaUsers\Entity\Student')->findOneBy(array('school' => $school, 'id' => $student));
                if ( $studentData ) {
                    $this->getEntityManager()->remove($studentData);
                    $this->getEntityManager()->flush();
                    $this->clearCache();
                    $this->clearUsersCache($school);
                    $this->clearCacheBySchool($school);
                }
            }
            $this->flashMessenger()->addMessage($translator->translate('Successfully deleted!'));
        }
        return $this->redirect()->toRoute('mySchools', array('action' => 'manage-students', 'id' => $school->getId()));
    }


    public function editStudentAction()
    {
        if ( $this->params('id')  == 0 ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $user = $this->identity();
        $school = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->getSchoolByIdAndUser($this->params('id'), $user);
        if ( !$school ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $student = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->findStudentBySchoolAndTid($school, $this->params('tid'));
        if ( !$student ) {
            throw new UnAuthorizedException('Wrong id');
        }

        $form = new EditStudent();
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');

        $classes = $student->getClasses();
        $classes = $this->formatTeacherClasses($classes);


        if ($request->isPost()) {
            $form->setHydrator(new DoctrineEntity($this->getEntityManager(), 'AmaUsers\Entity\Student'));
            $form->bind($student);

            $post = $request->getPost();
            $form->setInputFilter(new EditStudentFilter($this->getServiceLocator()));
            $form->setData($post);

            if ( $form->isValid() ) {
                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();


                foreach ( $post->get('studentClasses') as $class ) {
                    if(!in_array($class, $classes)) {
                        $schoolClassData = $this->getEntityManager()->getRepository('AmaSchools\Entity\SchoolClass')->findOneBy(array('school' => $school, 'id' => $class));
                        if ( $schoolClassData ) {
                            $studentClass = new StudentClass();
                            $studentClass->setSchool($school);
                            $studentClass->setClass($schoolClassData);
                            $studentClass->setTeacher($form->getData());
                            $this->getEntityManager()->persist($studentClass);
                            $this->getEntityManager()->flush();
                        }
                        unset($classes[$class]);

                    }
                    else {
                        unset($classes[$class]);
                    }
                }

                //remove classes that are not chosen
                foreach ( $classes  as $class ) {
                    $classData = $this->getEntityManager()->getRepository('AmaUsers\Entity\StudentClass')->findOneBy(array('school' => $school, 'student' => $form->getData(), 'class' => $class));
                    if($classData) {
                        $form->getData()->removeClass($classData);
                        $this->getEntityManager()->persist($form->getData());
                        $this->getEntityManager()->flush();
                    }
                }

                $this->clearCache();
                $this->clearUsersCache($school);
                $this->clearCacheBySchool($school);

                $this->flashMessenger()->addMessage($translator->translate('Successfully updated!'));
                return $this->redirect()->toRoute('mySchools', array('action' => 'edit-student', 'id' => $school->getId(),'tid' => $form->getData()->getId()));
            }

        }
        $classesList = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->findClassesBySchool($school);
        $this->layout()->setVariable('school', $school);
        return new ViewModel(array(
            'form' => $form,
            'school' => $school,
            'student' => $student,
            'classes' => $classesList,
            'chosenClasses' => $classes,
            'messages' => $this->formatMessage()->doFormat($form->getMessages()),
            'errorMessages' => $this->flashMessenger()->getErrorMessages(),
            'successMessages' => $this->flashMessenger()->getMessages()
        ));
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

    public function clearCacheBySchool($school)
    {
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaTeachersBySchoolPaginate'. $school->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaCountTeachersBySchool'. $school->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaStudentsBySchoolPaginate'. $school->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaCountStudentsBySchool'. $school->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSchoolClasses'. $school->getId());
    }

    private function formatClasses($classes)
    {
        $formatedClasses = array();
        foreach ( $classes as $class ) {
            $formatedClasses[$class->getClassName()] = $class->getClassName();
        }

        return $formatedClasses;
    }

    private function formatTeacherClasses($teacherClasses)
    {
        $formatedClasses = array();
        foreach ( $teacherClasses as $ta ) {
            $formatedClasses[$ta->getClass()->getId()] = $ta->getClass()->getId();
        }

        return $formatedClasses;
    }

}