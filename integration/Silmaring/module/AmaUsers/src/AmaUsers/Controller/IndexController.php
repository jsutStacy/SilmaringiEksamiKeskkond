<?php

namespace AmaUsers\Controller;

use AmaUsers\Entity\UserLogin;
use Zend\Filter\File\RenameUpload;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
use Zend\Http\Header\SetCookie;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use Zend\Crypt\Password\Bcrypt;
use Zend\Mime\Mime;

use AmaUsers\Form\LoginForm;
use AmaUsers\Form\LoginFilter;

use  Zend\Http\Header\Cookie;

use AmaUsers\Entity\User;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class IndexController extends AbstractActionController
{

    /**
     * Entitymanger
     * @var $em
     */
    protected $em;

    /**
     * AmaUsers\Entity\User
     * @var $user
     */
    protected $user;

    /**
     * Config
     * @var $config
     */
    protected $config;

    public function indexAction()
    {
        $this->getConfig();

        if ($this->user = $this->identity()) {
            return $this->redirect()->toRoute($this->config['redirect_after_login']);
        }

        $faceboookLogin = $this->getServiceLocator()->get('ReverseOAuth2\Facebook');
        $faceboookLogin->getOptions()->setScope(array('email', 'public_profile'));

        $googleLogin = $this->getServiceLocator()->get('ReverseOAuth2\Google');
        $googleLogin->getOptions()->setScope(array('email', 'profile'));

        $request = $this->getRequest();

        $cookie_email = '';
        if ( isset($request->getCookie()->svipe_remember_email) )
        $cookie_email = $request->getCookie()->svipe_remember_email;

        $this->layout('layout/frontend');
        $form = new LoginForm();

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

        $login = $request->getQuery('login');
        $type = $request->getQuery('type');

        $this->layout()->setVariable('login', $login);
        $this->layout()->setVariable('type', $type);

        return new ViewModel(array(
            'form' => $form,
            'email' => $cookie_email,
            'facebookLoginUrl' => $faceboookLogin->getUrl(),
            'googleLoginUrl' => $googleLogin->getUrl(),
            'faceboookError' => $faceboookError,
            'googleError' => $googleError,
            'errorMessages' => $this->flashMessenger()->getErrorMessages(),
            'username' => $request->getQuery('username'),
            'password' => $request->getQuery('password')
        ));
    }

    public function loginAction()
    {
        $this->getConfig();

        if ($this->user = $this->identity()) {
            return $this->redirect()->toRoute($this->config['redirect_after_login']);
        }

        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('home');
        }

        $translator = $this->getServiceLocator()->get('translator');
        $form = new LoginForm();
        $messages = null;

        $request = $this->getRequest();
        if ($request->isPost()) {

            $form->setInputFilter(new LoginFilter($this->getServiceLocator()));
            $form->setData($request->getPost());

            if (!$form->isValid()) {
                return new JsonModel(array(
                    'success' => false,
                    'message' => $this->formatMessage($form->getMessages())
                ));
            }
            $data = $form->getData();
            $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');

            // check for email first
            $user = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findOneBy(array('email' => $data['email']));
            if (!$user) {
                return new JsonModel(array(
                    'success' => false,
                    'message' => $translator->translate("Wrong email or password!")
                ));
            }

            //check if active
            if ( $user->getState()!=1 ) {
                return new JsonModel(array(
                    'success' => false,
                    'message' => $translator->translate("Account not active. Please contact admin.")
                ));
            }

            $adapter = $authService->getAdapter();
            $adapter->setIdentityValue($data['email']);
            $adapter->setCredentialValue($this->config['static_salt'] . $data['password'] . $user->getDynamicSalt());
            $authResult = $authService->authenticate();

            if ($authResult->isValid()) {
                $identity = $authResult->getIdentity();
                $authService->getStorage()->write($identity);

                if ($data['rememberme']) {
                    $time = time() + 365 * 60 * 60 * 24; //one year
                    $cookie = new SetCookie('svipe_remember_email', $user->getEmail(), $time);
                    $response = $this->getResponse()->getHeaders();
                    $response->addHeader($cookie);
                }

                //add user login date and time
                $userLogin = new UserLogin();
                $userLogin->setUser($user);
                $this->getEntityManager()->persist($userLogin);
                $this->getEntityManager()->flush();

                $user->setUserLastLogin($userLogin);
                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush();


                return new JsonModel(array(
                    'success' => true,
                    'message' => $translator->translate("Successful login!")
                ));
            }

            //temp password login
            $tempPassword = $user->getTempPassword();
            if (!empty($tempPassword)) {

                $adapter->setIdentityValue($data['email']);
                $adapter->setCredentialValue($this->config['static_salt'] . $data['password'] . $user->getTempDynamicSalt());
                $authResult = $authService->authenticate();

                if ($authResult->isValid()) {
                    $identity = $authResult->getIdentity();
                    $authService->getStorage()->write($identity);

                    //change password to from temp to original.
                    $user->setPassword($user->getTempPassword());
                    $user->setDynamicSalt($user->getTempDynamicSalt());
                    $user->setTempPassword('');
                    $user->setTempDynamicSalt('');
                    $this->getEntityManager()->persist($user);
                    $this->getEntityManager()->flush();


                    if ($data['rememberme']) {
                        $time = time() + 365 * 60 * 60 * 24; //one year
                        $cookie = new SetCookie('svipe_remember_email', $user->getEmail(), $time);
                        $response = $this->getResponse()->getHeaders();
                        $response->addHeader($cookie);
                    }

                    return new JsonModel(array(
                        'success' => true,
                        'message' => $translator->translate("Successful login!")
                    ));
                }
            }

        }

        return new JsonModel(array(
            'success' => false,
            'message' => $translator->translate("Wrong email or password!")
        ));
    }

    public function logoutAction()
    {
        $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
        }

        $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->updateOnlineTime($identity);

        $auth->clearIdentity();
        $sessionManager = new SessionManager();
        $sessionManager->forgetMe();

        return $this->redirect()->toRoute('home');

    }

    public function facebookLoginAction()
    {
        $this->layout('layout/frontend');
        $me = $this->getServiceLocator()->get('ReverseOAuth2\Facebook');
        if (strlen($this->params()->fromQuery('code')) > 10) {
            $request = $this->getRequest();
            if($me->getToken($request)) {
                $token = $me->getSessionToken(); // token in session
            } else {
                $token = $me->getError(); // last returned error (array)
            }

            $info = $me->getInfo();

            if ( !$info->email ) {
                $session = new Container('ama_facebook');
                $session->facebookError = true;
                return $this->redirect()->toRoute('home');
            }

            $user = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findOneBy(array("email" => $info->email));
            if (!$user) {
                $this->loginAndRegisterUser($info);
            }
            else {
                $user->setFbId($info->id);

                if(!$user->getImage()) {
                    $user->setImage($this->getFacebookImage($info->id));
                }

                $this->loginUser($user);
            }
        }
        else {
            $session = new Container('ama_facebook');
            $session->facebookError = true;
            return $this->redirect()->toRoute('home');
        }
    }

    public function testFbImageAction() {
        $this->getFbImageByCurl('846517822054462');
        return new JsonModel();
    }

    public function getFacebookImage($userId)
    {
        $this->getConfig();
        $image = $this->getFbImageByCurl($userId);
        if(empty($image)) return '';
        return $image;
    }

    public function getGoogleImage($userId)
    {
        $this->getConfig();
        $image = $this->getGImageByCurl($userId);
        if(empty($image)) return '';
        return $image;
    }

    public function getFbImageByCurl($userId)
    {
        $client = new Client("http://graph.facebook.com/$userId/picture?width=150&height=150&redirect=false");
        $adapter = new Curl();
        $client->setAdapter($adapter);

        $adapter->setOptions(array(
            'curloptions' => array(
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_SSL_VERIFYHOST => FALSE,
            )
        ));
        $client->setAdapter($adapter);

        $response = $client->send($client->getRequest());
        $body = $response->getBody();
        if(empty($body)) {
            return '';
        }
        $data =  Json::decode($body);
        if(!isset($data->data->url)) return '';

        //Download file
        $ex = end(explode('.', $data->data->url));
        $ex = explode('?', $ex);
        $ext = $ex[0];
        $randImage = uniqid(mktime()) . '.' . $ext;
        $destLocation = $this->config['profile_image_dir'].$randImage;
        $this->customFileDownload($data->data->url, $destLocation);

        return $randImage;
    }


    public function getGImageByCurl($userId)
    {
        $client = new Client("https://www.googleapis.com/plus/v1/people/$userId?fields=image&key=" . $this->config['google_api_key']);
        $adapter = new Curl();
        $client->setAdapter($adapter);

        $adapter->setOptions(array(
            'curloptions' => array(
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_SSL_VERIFYHOST => FALSE,
            )
        ));
        $client->setAdapter($adapter);

        $response = $client->send($client->getRequest());
        $body = $response->getBody();
        if(empty($body)) {
            return '';
        }
        $data =  Json::decode($body);
        if(!isset($data->image->url)) return '';

        //Download file
        $ex = end(explode('.', $data->image->url));
        $ex = explode('?', $ex);
        $ext = $ex[0];
        $randImage = uniqid(mktime()) . '.' . $ext;
        $destLocation = $this->config['profile_image_dir'].$randImage;
        $this->customFileDownload($data->image->url, $destLocation);

        return $randImage;
    }


    public function googleLoginAction()
    {
        $this->layout('layout/frontend');
        $me = $this->getServiceLocator()->get('ReverseOAuth2\Google');
        if (strlen($this->params()->fromQuery('code')) > 10) {
            $request = $this->getRequest();
            if($me->getToken($request)) {
                $token = $me->getSessionToken(); // token in session
            } else {
                $token = $me->getError(); // last returned error (array)
            }

            $info = $me->getInfo();
            if ( !$info->email ) {
                $session = new Container('ama_google');
                $session->googleError = true;
                return $this->redirect()->toRoute('home');
            }

            $user = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findOneBy(array("email" => $info->email));
            if (!$user) {
                $this->loginAndRegisterUser($info, 'google');
            }
            else {
                $user->setFbId($info->id);

                if(!$user->getImage())
                       $user->setImage($this->getGoogleImage($info->id));

                $this->loginUser($user);
            }
        }
        else {
            $session = new Container('ama_google');
            $session->googleError = true;
            return $this->redirect()->toRoute('home');
        }
    }

    private function loginUser($user)
    {
        //check if active
        if ( $user->getState()!=1 ) {
            $translator = $this->getServiceLocator()->get('translator');
            $this->flashMessenger()->addErrorMessage($translator->translate('Account not active. Please contact admin.'));
            $this->redirect()->toRoute('home');
        }

        $oldPassword = $user->getPassword();
        $oldSalt = $user->getDynamicSalt();

        $this->getConfig();

        $user->setPassword(
            $this->encryptPassword(
                $this->config['static_salt'],
                $password = $this->generatePassword(),
                $dynamicSalt = $this->generateDynamicSalt()));
        $user->setDynamicSalt($dynamicSalt);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        //log user in
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $adapter = $authService->getAdapter();
        $adapter->setIdentityValue($user->getEmail());
        $adapter->setCredentialValue($this->config['static_salt'] . $password . $dynamicSalt);
        $authResult = $authService->authenticate();

        if ($authResult->isValid()) {
            $identity = $authResult->getIdentity();
            $authService->getStorage()->write($identity);

            //add user login date and time
            $userLogin = new UserLogin();
            $userLogin->setUser($user);
            $this->getEntityManager()->persist($userLogin);
            $this->getEntityManager()->flush();

            $user->setUserLastLogin($userLogin);
        }

        //set back to old password
        $user->setPassword($oldPassword);
        $user->setDynamicSalt($oldSalt);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $this->redirect()->toRoute('home');
    }

    private function loginAndRegisterUser($post, $type = 'facebook')
    {

        $this->getConfig();
        $translator = $this->getServiceLocator()->get('translator');

        $role = $this->getEntityManager()->getRepository('AmaUsers\Entity\Role')->findOneBy(array('roleId' => $this->config['default_role_social_login']));
        $user = new User();
        $user->addRole($role);
        $user->setEmail($post->email);
        if ( $type == 'google' ) {
            $user->setFirstName($post->given_name);
            $user->setLastname($post->family_name);
        }
        else {
            $user->setFirstName($post->first_name);
            $user->setLastname($post->last_name);
        }
        $user->setDisplayName($user->getFirstName() . ' ' . $user->getLastname());

        if ( $type == 'facebook' )
            $user->setFbId($post->id);

        if ( $type == 'google' )
            $user->setGoogleId($post->id);

        $user->setState(1);
        $user->setPassword(
            $this->encryptPassword(
                $this->config['static_salt'],
                $password = $this->generatePassword(),
                $dynamicSalt = $this->generateDynamicSalt()));
        $user->setDynamicSalt($dynamicSalt);


        if(!$user->getImage()) {
            if ( $type == 'facebook' )
                 $user->setImage($this->getFacebookImage($post->id));

            if ( $type == 'google' )
                $user->setImage($this->getGoogleImage($post->id));

        }

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
        $body = $translator->translate('Your account has been registred and activated.') . '<br>';
        $body .= $translator->translate('Your login email is') . ' <strong>' . $user->getEmail() . '</strong><br>';
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
        $mail->addTo($post->email);
        $mail->setSubject($translator->translate("Thank you for registering!"));

        $transport = new Sendmail();
        $transport->send($mail);

        //notify admin new user
        $mail = new Message();
        $mail->setBody($translator->translate("New user registred") . ' ' . $post->email);
        $mail->setFrom($this->config['mail']['from_mail'], $this->config['mail']['from_name']);
        $mail->addTo($this->config['admin_notify_mail']);
        $mail->setSubject($translator->translate("New user registred"));

        $transport = new Sendmail();
        $transport->send($mail);

        //log user in
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $adapter = $authService->getAdapter();
        $adapter->setIdentityValue($post->email);
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

        $user->setUserLastLogin($userLogin);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $user->setPassword(
            $this->encryptPassword(
                $this->config['static_salt'],
                $password = $this->generatePassword(),
                $dynamicSalt = $this->generateDynamicSalt()));

        return $this->redirect()->toRoute('home');
    }

    public function getEntityManager()
    {
        if ($this->em) return $this->em;
        $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        return $this->em;
    }

    public function getConfig()
    {
        if (!$this->config) {
            return $this->config = $this->getServiceLocator()->get('config');
        }
        return $this->config;
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


    public function generateDynamicSalt()
    {
        $dynamicSalt = '';
        for ($i = 0; $i < 50; $i++) {
            $dynamicSalt .= chr(rand(33, 126));
        }

        return $dynamicSalt;
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

    /**
     * @param $source
     * @param $destination
     */
    public function customFileDownload($source, $destination)
    {
        $ch = curl_init($source);
        $fp = fopen($destination, "wb");

        // set URL and other appropriate options
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
        $options = array(CURLOPT_FILE => $fp,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $agent);

        curl_setopt_array($ch, $options);

        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }
}
