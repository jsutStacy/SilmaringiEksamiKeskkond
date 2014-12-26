<?php
namespace AmaUsers\Controller;

use AmaLoyalty\Entity\Subscription;
use AmaUsers\Entity\UserLogin;
use AmaUsers\Form\ForgotPasswordForm;
use Zend\Crypt\Password\Bcrypt;
use Zend\Mime\Mime;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use Zend\Session\Container;
use Zend\Session\SessionManager;

use AmaUsers\Form\RegisterForm;
use AmaUsers\Form\RegisterFilter;
use AmaUsers\Form\ForgottenPasswordForm;
use AmaUsers\Form\ForgottenPasswordFilter;

use AmaUsers\Entity\User;

class RegisterController extends AbstractActionController
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

        if ($user = $this->identity()) {
            return $this->redirect()->toRoute('dashboard');
        }
        $translator = $this->getServiceLocator()->get('translator');
        $this->layout('layout/frontend');


        $faceboookLogin = $this->getServiceLocator()->get('ReverseOAuth2\Facebook');
        $faceboookLogin->getOptions()->setScope(array('email', 'public_profile'));

        $googleLogin = $this->getServiceLocator()->get('ReverseOAuth2\Google');
        $googleLogin->getOptions()->setScope(array('email', 'profile'));

        $session = new Container('ama_facebook');
        $faceboookError = false;
        if(isset($session->facebookError)) {
            $faceboookError = true;
            $session->facebookError = null;
        }

        $session = new Container('ama_google');
        $googleError = false;
        if(isset($session->googleError)) {
            $googleError = true;
            $session->googleError = null;
        }

        $form = new RegisterForm();
        $form->get('role')->setValueOptions(
            array(
                'v_student' => $translator->translate('Student'),
                'v_teacher' => $translator->translate('Teacher')
            )
        );
        return new ViewModel(array(
            'form' => $form,
            'facebookLoginUrl' => $faceboookLogin->getUrl(),
            'googleLoginUrl' => $googleLogin->getUrl(),
            'faceboookError' => $faceboookError,
            'googleError' => $googleError
        ));
    }

    public function forgottenPasswordAction()
    {
        if ($this->user = $this->identity()) {
            return $this->redirect()->toRoute($this->config['logged_in_redirect']);
        }
        $this->layout('layout/frontend');
        $form = new ForgottenPasswordForm();
        return new ViewModel(array(
            'form' => $form
        ));
    }

    public function registerAjaxAction()
    {
        $request = $this->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('home');
        }

        if (!$request->isPost()) {
            return $this->redirect()->toRoute('error');
        }

        $translator = $this->getServiceLocator()->get('translator');
        $post = $request->getPost();
        $form = new RegisterForm();
        $form->setInputFilter(new RegisterFilter($this->getServiceLocator()));
        $form->setData($post);
        $form->get('role')->setValueOptions(
            array(
                'v_student' => $translator->translate('Student'),
                'v_teacher' => $translator->translate('Teacher')
            )
        );

        if (!$form->isValid()) {
            return new JsonModel(array(
                'success' => false,
                'message' => $this->formatMessage($form->getMessages())
            ));
        }

        $this->getConfig();

        $role = $this->getEntityManager()->getRepository('AmaUsers\Entity\Role')->findOneBy(array('roleId' => $post->get('role')));
        if ( !$role ) {
            return new JsonModel(array(
                'success' => false,
                'message' => $translator->translate("Oops! Something went wrong. Please contact admin.")
            ));
        }

        $user = new User;
        $user->addRole($role);
        $user->setEmail($post->get('email'));
        $user->setFirstName($post->get('first_name'));
        $user->setLastname($post->get('lastname'));
        $user->setDisplayName($user->getFirstName() . ' ' . $user->getLastname());
        $user->setState(1);
        $user->setPassword(
            $this->encryptPassword(
                $this->config['static_salt'],
                $password = $this->generatePassword(),
                $dynamicSalt = $this->generateDynamicSalt()));
        $user->setDynamicSalt($dynamicSalt);
        try {
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
            $this->clearCache();
        } catch (Exception $e) {
            return new JsonModel(array(
                'success' => false,
                'message' => $translator->translate("Oops! Something went wrong. Please contact admin. ")
            ));
        }

        //send message to client with password
        $body = $translator->translate('Your account has been registred and activated.') . '<br>';
        $body .= $translator->translate('We generated you a password') . ' <strong>' . $password . '</strong><br>';
        $body .= '<br>' . $translator->translate('Silmaring team');

        $html = new MimePart($body);
        $html->type = "text/html";
        $html->charset = 'utf-8';

        $messgeBody = new MimeMessage();
        $messgeBody->setParts(array($html));

        $mail = new Message();
        $mail->setEncoding('UTF-8');
        $mail->setBody($messgeBody);
        $mail->setFrom($this->config['mail']['from_mail'], $this->config['mail']['from_name']);
        $mail->addTo($post->get('email'));
        $mail->setSubject($translator->translate("Thank you for registering!"));

        $transport = new Sendmail();
        $transport->send($mail);

        //notify admin new user
        $mail = new Message();
        $mail->setBody($translator->translate("New user registred") . ' ' . $post->get('email'));
        $mail->setFrom($this->config['mail']['from_mail'], $this->config['mail']['from_name']);
        $mail->addTo($this->config['admin_notify_mail']);
        $mail->setSubject($translator->translate("New user registred"));

        $transport = new Sendmail();
        $transport->send($mail);

        //log user in
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $adapter = $authService->getAdapter();
        $adapter->setIdentityValue($post->get('email'));
        $adapter->setCredentialValue($this->config['static_salt'] . $password . $dynamicSalt);
        $authResult = $authService->authenticate();

        if (!$authResult->isValid()) {
            return new JsonModel(array(
                'success' => false,
                'message' => $translator->translate("Oops! Something went wrong. Please contact admin.")
            ));
        }

        //add user login date and time
        $userLogin = new UserLogin();
        $userLogin->setUser($user);
        $this->getEntityManager()->persist($userLogin);
        $this->getEntityManager()->flush();

        $user->setPassword(
            $this->encryptPassword(
                $this->config['static_salt'],
                $password = $this->generatePassword(),
                $dynamicSalt = $this->generateDynamicSalt()));

        return new JsonModel(array(
            'success' => true,
            'message' => $translator->translate("Successful login! You will be redirected in a second.")
        ));
    }

    public function forgottenPasswordAjaxAction()
    {
        $request = $this->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('home');
        }

        if (!$request->isPost()) {
            return $this->redirect()->toRoute('error');
        }
        $this->getConfig();
        $translator = $this->getServiceLocator()->get('translator');
        $post = $request->getPost();
        $form = new ForgottenPasswordForm();
        $form->setInputFilter(new ForgottenPasswordFilter($this->getServiceLocator()));
        $form->setData($post);

        if (!$form->isValid()) {
            return new JsonModel(array(
                'success' => false,
                'message' => $this->formatMessage($form->getMessages())
            ));
        }

        $data = $form->getData();
        $user = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findOneBy(array('email' => $data['email']));
        if (!$user) {
            return new JsonModel(array(
                'success' => false,
                'message' => $translator->translate("Wrong email!")
            ));
        }

        $user->setTempPassword(
            $this->encryptPassword(
                $this->config['static_salt'],
                $password = $this->generatePassword(),
                $dynamicSalt = $this->generateDynamicSalt()));
        $user->setTempDynamicSalt($dynamicSalt);

        try {
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        } catch (Exception $e) {
            return new JsonModel(array(
                'success' => false,
                'message' => $translator->translate("Oops! Something went wrong. Please contact admin.")
            ));
        }

        //send message to client with password
        $body = $translator->translate('Your new password is here!') . '<br>';
        $body .= $translator->translate('We generated you a new password') . ' <strong>' . $password . '</strong><br>';
        $body .= '<br>' . $translator->translate('Silmaring team');

        $html = new MimePart($body);
        $html->type = "text/html";
        $html->charset = 'utf-8';

        $messgeBody = new MimeMessage();
        $messgeBody->setParts(array($html));

        $mail = new Message();
        $mail->setEncoding('utf-8');
        $mail->setBody($messgeBody);
        $mail->setFrom($this->config['mail']['from_mail'], $this->config['mail']['from_name']);
        $mail->addTo($data['email']);
        $mail->setSubject($translator->translate("Your new password!"));

        $transport = new Sendmail();
        $transport->send($mail);

        return new JsonModel(array(
            'success' => true,
            'message' => $translator->translate("Success! New password sent to your email.")
        ));

    }

    public function generateDynamicSalt()
    {
        $dynamicSalt = '';
        for ($i = 0; $i < 50; $i++) {
            $dynamicSalt .= chr(rand(33, 126));
        }

        return $dynamicSalt;
    }

    public function encryptPassword($staticSalt, $password, $dynamicSalt)
    {
        $bcrypt = new Bcrypt();
        return $bcrypt->create($staticSalt . $password . $dynamicSalt);
    }

    public function generatePassword($l = 8, $c = 0, $n = 0, $s = 0)
    {
        // get count of all required minimum special chars
        $count = $c + $n + $s;
        $out = '';
        // sanitize inputs; should be self-explanatory
        if (!is_int($l) || !is_int($c) || !is_int($n) || !is_int($s)) {
            trigger_error('Argument(s) not an integer', E_USER_WARNING);

            return false;
        } elseif ($l < 0 || $l > 20 || $c < 0 || $n < 0 || $s < 0) {
            trigger_error('Argument(s) out of range', E_USER_WARNING);

            return false;
        } elseif ($c > $l) {
            trigger_error('Number of password capitals required exceeds password length', E_USER_WARNING);

            return false;
        } elseif ($n > $l) {
            trigger_error('Number of password numerals exceeds password length', E_USER_WARNING);

            return false;
        } elseif ($s > $l) {
            trigger_error('Number of password capitals exceeds password length', E_USER_WARNING);

            return false;
        } elseif ($count > $l) {
            trigger_error('Number of password special characters exceeds specified password length', E_USER_WARNING);

            return false;
        }

        // all inputs clean, proceed to build password

        // change these strings if you want to include or exclude possible password characters
        $chars = "abcdefghijklmnopqrstuvwxyz";
        $caps = strtoupper($chars);
        $nums = "0123456789";
        $syms = "!@#$%^&*()-+?";

        // build the base password of all lower-case letters
        for ($i = 0; $i < $l; $i++) {
            $out .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        // create arrays if special character(s) required
        if ($count) {
            // split base password to array; create special chars array
            $tmp1 = str_split($out);
            $tmp2 = array();

            // add required special character(s) to second array
            for ($i = 0; $i < $c; $i++) {
                array_push($tmp2, substr($caps, mt_rand(0, strlen($caps) - 1), 1));
            }
            for ($i = 0; $i < $n; $i++) {
                array_push($tmp2, substr($nums, mt_rand(0, strlen($nums) - 1), 1));
            }
            for ($i = 0; $i < $s; $i++) {
                array_push($tmp2, substr($syms, mt_rand(0, strlen($syms) - 1), 1));
            }

            // hack off a chunk of the base password array that's as big as the special chars array
            $tmp1 = array_slice($tmp1, 0, $l - $count);
            // merge special character(s) array with base password array
            $tmp1 = array_merge($tmp1, $tmp2);
            // mix the characters up
            shuffle($tmp1);
            // convert to string for output
            $out = implode('', $tmp1);
        }

        return $out;
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

    public function clearCache()
    {
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsers');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsersPaginate');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaUsersCount');
    }
}