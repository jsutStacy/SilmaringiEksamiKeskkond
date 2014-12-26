<?php
namespace AmaUsers\Controller;

use AmaUsers\Entity\UserCategory;
use AmaUsers\Entity\UserStudent;
use AmaUsers\Entity\UserTeacher;
use AmaUsers\Form\SettingsFilter;
use AmaUsers\Form\SettingsForm;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity;
use Zend\Crypt\Password\Bcrypt;
use Zend\Filter\File\RenameUpload;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SettingsController extends AbstractActionController
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

        $allCategories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllCategoriesByDepth();
        $allCategories = $this->formatCategories()->getCategoryTree($allCategories);

        $form = new SettingsForm();
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');
        $user = $this->identity();
        $this->getConfig();
        $userCategories = $this->getEntityManager()->getRepository('AmaUsers\Entity\UserCategory')->getUserCategories($user);

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

            $files = $request->getFiles('image');
            if (!empty($files['name'])) {
                $form->getInputFilter()->get('image')->setRequired(true);
            }

            if ($form->get('currentPassword')->getValue() || $form->get('newPassword')->getValue() || $form->get('newPasswordConfirm')->getValue()) {
                $form->getInputFilter()->get('currentPassword')->setRequired(true);
                $form->getInputFilter()->get('newPassword')->setRequired(true);
                $form->getInputFilter()->get('newPasswordConfirm')->setRequired(true);
            }


            if ($form->isValid()) {

                if ($form->get('currentPassword')->getValue()) {

                    //is current password valid
                    $userservice = $this->getServiceLocator()->get('userservice');
                    $currentPassword = $this->config['static_salt'] . $form->getInputFilter()->get('currentPassword')->getValue() . $user->getDynamicSalt();
                    if (!$userservice->verifyUser($user, $currentPassword)) {
                        $this->flashMessenger()->addErrorMessage($translator->translate('Wrong current password!'));
                        return $this->redirect()->toRoute('settings');
                    }

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
                if (is_array($form->getData()->getImage()))
                    $form->getData()->setImage($imageExists);


                //change user role based on personal code added or not
                $roleId = $form->getData()->getSingleRole()->getRoleId();
                $personalCodeHash = $form->getData()->getPersonalCodeHash();
                if ($personalCodeHash) {
                    if ($roleId != 'school') {
                        if ($roleId == 'v_student' || $roleId == 'k_student') {
                            $role = $this->connectUserWithStudent($form->getData()->getSingleRole(), $form->getData());
                            $form->getData()->getRoles()->removeElement($form->getData()->getSingleRole());
                            $form->getData()->addRole($role);
                        } else {
                            $role = $this->connectUserWithTeacher($form->getData()->getSingleRole(), $form->getData());
                            $form->getData()->getRoles()->removeElement($form->getData()->getSingleRole());
                            $form->getData()->addRole($role);
                        }
                    }
                } else {
                    if ($roleId != 'school') {
                        if ($roleId == 'v_student' || $roleId == 'k_student') {
                            $role = $this->disconnectUserWithStudent($form->getData());
                            $role = $this->getEntityManager()->getRepository('AmaUsers\Entity\Role')->findOneBy(array('roleId' => 'v_student'));
                            $form->getData()->getRoles()->removeElement($form->getData()->getSingleRole());
                            $form->getData()->addRole($role);
                        } else {
                            $role = $this->disconnectUserWithTeacher($form->getData());
                            $role = $this->getEntityManager()->getRepository('AmaUsers\Entity\Role')->findOneBy(array('roleId' => 'v_teacher'));
                            $form->getData()->getRoles()->removeElement($form->getData()->getSingleRole());
                            $form->getData()->addRole($role);
                        }
                    }
                }

                //user categories in left menu
                $origUserCategories = $userCategories;
                if ($post['categories']) {

                    foreach ($post['categories'] as $category) {
                        $category = $this->cleanInputs()->clean($category);
                        $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($category);
                        if (!$category) continue;

                        if (in_array($category->getId(), $origUserCategories)) {
                            unset($origUserCategories[$category->getId()]);
                        }
                        else {
                            $userCategory = new UserCategory();
                            $userCategory->setUser($user);
                            $userCategory->setCategory($category);
                            $userCategory->setDateModified(new \DateTime());
                            $this->getEntityManager()->persist($userCategory);
                        }
                    }
                }

                foreach ($origUserCategories as $cat) {
                    $userCategory = $this->getEntityManager()->getRepository('AmaUsers\Entity\UserCategory')->findOneBy(array('user' => $user, 'category' => $cat));
                    $this->getEntityManager()->remove($userCategory);
                }

                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();
                $this->clearCache();

                $this->flashMessenger()->addMessage($translator->translate('Successfully updated!'));
                return $this->redirect()->toRoute('settings');
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'user' => $user,
            'messages' => $this->formatMessage($form->getMessages()),
            'successMessages' => $this->flashMessenger()->getMessages(),
            'errorMessages' => $this->flashMessenger()->getErrorMessages(),
            'imagePath' => $this->config['profile_image_dir'],
            'categoryTree' => $allCategories,
            'userCategories' => $userCategories
        ));

    }

    /**
     * @return \Zend\Http\Response
     */
    public function deleteProfileImageAction()
    {
        $user = $this->identity();
        $this->getConfig();
        $image = $user->getImage();
        if (!empty($image)) {
            $imageFile = $this->config['profile_image_dir'] . $user->getImage();
            if (file_exists($imageFile)) {
                @unlink($imageFile);
            }

            $user->setImage('');
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
            $this->clearCache();
        }
        return $this->redirect()->toRoute('settings');
    }

    /**
     * When we find teacher with same personal code we connect user to it
     *
     * @param $role
     * @param $user
     * @return mixed
     */
    public function connectUserWithTeacher($role, $user)
    {
        $translator = $this->getServiceLocator()->get('translator');
        $isTeacher = false;
        $teacherSchools = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->findTeacherSchoolsByUser($user);
        foreach ($teacherSchools as $teacherSchool) {
            $isTeacher = true;
            $userTeacher = new UserTeacher();
            $userTeacher->setTeacher($teacherSchool);
            $userTeacher->setUser($user);
            $this->getEntityManager()->persist($userTeacher);
            $this->flashMessenger()->addMessage($translator->translate('You where added as teacher to school') . $teacherSchool->getSchool()->getName());
            $this->clearCacheBySchool($teacherSchool->getSchool());
        }
        $this->getEntityManager()->flush();

        if ($isTeacher) {
            $role = $this->getEntityManager()->getRepository('AmaUsers\Entity\Role')->findOneBy(array('roleId' => 'k_teacher'));
        }
        return $role;
    }

    /**
     * @param $user
     */
    public function disconnectUserWithTeacher($user)
    {
        $userTeacher = $this->getEntityManager()->getRepository('AmaUsers\Entity\UserTeacher')->findBy(array('user' => $user));
        foreach ($userTeacher as $teacher) {
            $user->removeTeacher($teacher);
            $this->getEntityManager()->flush();
            $this->clearCacheBySchool($teacher->getTeacher()->getSchool());
        }
    }


    /**
     * When we find student with same personal code we connect user to it
     *
     * @param $role
     * @param $user
     * @return mixed
     */
    public function connectUserWithStudent($role, $user)
    {
        $translator = $this->getServiceLocator()->get('translator');
        $isStudent = false;
        $studentSchools = $this->getEntityManager()->getRepository('AmaSchools\Entity\School')->findStudentSchoolsByUser($user);
        foreach ($studentSchools as $studentSchool) {
            $isStudent = true;
            $userStudent = new UserStudent();
            $userStudent->setStudent($studentSchool);
            $userStudent->setUser($user);
            $this->getEntityManager()->persist($userStudent);
            $this->flashMessenger()->addMessage($translator->translate('You where added as student to school') . $studentSchool->getSchool()->getName());
            $this->clearCacheBySchool($studentSchool->getSchool());
        }
        $this->getEntityManager()->flush();

        if ($isStudent) {
            $role = $this->getEntityManager()->getRepository('AmaUsers\Entity\Role')->findOneBy(array('roleId' => 'k_student'));
        }
        return $role;
    }

    /**
     * @param $user
     */
    public function disconnectUserWithStudent($user)
    {
        $userStudent = $this->getEntityManager()->getRepository('AmaUsers\Entity\UserStudent')->findBy(array('user' => $user));
        foreach ($userStudent as $student) {
            $user->removeStudent($student);
            $this->getEntityManager()->flush();
            $this->clearCacheBySchool($student->getStudent()->getSchool());
        }
    }

    /**
     * Format error messages
     * @param $messages
     * @return string
     */
    public function formatMessage($messages = '')
    {
        $return = '';
        if (is_array($messages)) {
            foreach ($messages as $message) {
                if (is_array($message)) {
                    foreach ($message as $m) {
                        $return .= $m . '<br>';
                    }
                } else {
                    $return .= $message . '<br>';
                }
            }
        } else {
            $return = $messages;
        }
        return $return;
    }

    public function getConfig()
    {
        if (!$this->config) {
            return $this->config = $this->getServiceLocator()->get('config');
        }
        return $this->config;
    }

    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

    /**
     * @param $staticSalt
     * @param $password
     * @param $dynamicSalt
     * @return string
     */
    public function encryptPassword($staticSalt, $password, $dynamicSalt)
    {
        $bcrypt = new Bcrypt();
        return $bcrypt->create($staticSalt . $password . $dynamicSalt);
    }

    /**
     * @param $fullFilePath
     * @return mixed|string
     */
    public function extractFilename($fullFilePath)
    {
        $parts = explode("/", $fullFilePath);
        if (!is_array($parts)) return '';
        return end($parts);
    }

    public function clearCache()
    {
        $user = $this->identity();
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsers');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsersPaginate');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsersCount');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('UserRoles' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUserCategories' . $user->getId());
        $cache = $this->getServiceLocator()->get('zcache');
        $cache->clearByPrefix('category');
    }

    public function clearCacheBySchool($school)
    {
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaTeachersBySchoolPaginate' . $school->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaCountTeachersBySchool' . $school->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaStudentsBySchoolPaginate' . $school->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaCountStudentsBySchool' . $school->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSchoolClasses' . $school->getId());
    }
}