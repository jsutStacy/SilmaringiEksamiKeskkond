<?php

namespace EksamiKeskkond\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Mail;
use Zend\Mail\Message;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;

use EksamiKeskkond\Model\User;
use EksamiKeskkond\Form\RegisterForm;
use EksamiKeskkond\Form\ForgottenPasswordForm;
use EksamiKeskkond\Filter\RegisterFilter;
use EksamiKeskkond\Filter\ForgottenPasswordFilter;

class RegisterController extends AbstractActionController {

	protected $userTable;

	public function indexAction() {
		$form = new RegisterForm();
		$request = $this->getRequest();

		if ($request->isPost()) {
			$form->setInputFilter(new RegisterFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$data = $form->getData();
				$data = $this->prepareData($data);

				$user = new User();
				$user->exchangeArray($data);

				$this->getUserTable()->saveUser($user);
				$this->sendConfirmationEmail($user);
				$this->flashMessenger()->addMessage($user->email);

				return $this->redirect()->toRoute('register/success');
			}
		}
		return array('form' => $form);
	}

	public function successAction() {
		$email = null;
		$flashMessenger = $this->flashMessenger();

		if ($flashMessenger->hasMessages()) {
			foreach ($flashMessenger->getMessages() as $key => $value) {
				$email .= $value;
			}
		}
		return new ViewModel(array('email' => $email));
	}

	public function forgottenPasswordAction() {
		$form = new ForgottenPasswordForm();
		$request = $this->getRequest();

		if ($request->isPost()) {
			$form->setInputFilter(new ForgottenPasswordFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$data = $form->getData();
				$email = $data['email'];

				$userTable = $this->getUserTable();

				$user = $userTable->getUserByEmail($email);
				$password = $this->generatePassword();

				$user->password = $this->encriptPassword($this->getStaticSalt(), $password, $user->password_salt);

				$userTable->saveUser($user);

				$this->sendPasswordByEmail($email, $password);
				$this->flashMessenger()->addMessage($email);

				return $this->redirect()->toRoute('register/password-change-success');
			}
		}
		return new ViewModel(array('form' => $form));
	}

	public function passwordChangeSuccessAction() {
		$email = null;
		$flashMessenger = $this->flashMessenger();

		if ($flashMessenger->hasMessages()) {
			foreach ($flashMessenger->getMessages() as $key => $value) {
				$email .= $value;
			}
		}
		return new ViewModel(array('email' => $email));
	}

	public function confirmEmailAction() {
		$token = $this->params()->fromRoute('token');
		$viewModel = new ViewModel(array('token' => $token));

		try {
			$user = $this->getUserTable()->getUserByToken($token);
			$id = $user->id;

			$this->getUserTable()->activateUser($id);
		}
		catch(\Exception $e) {
			$viewModel->setTemplate('eksami-keskkond/register/confirm-email-error.phtml');
		}
		return $viewModel;
	}

	public function prepareData($data) {
		$data['role_id'] = 4;
		$data['email_confirmed'] = 0;
		$data['password_salt'] = $this->generateDynamicSalt();
		$data['password'] = $this->encriptPassword(
			$this->getStaticSalt(),
			$data['password'],
			$data['password_salt']
		);
		$data['status'] = 0;
		$date = new \DateTime();
		$data['registration_date'] = $date->format('Y-m-d H:i:s');
		$data['registration_token'] = md5(uniqid(mt_rand(), true));

		return $data;
	}

	public function generateDynamicSalt() {
		$dynamicSalt = '';

		for ($i = 0; $i < 50; $i++) {
			$dynamicSalt .= chr(rand(33, 126));
		}
		return $dynamicSalt;
	}

	public function getStaticSalt() {
		$staticSalt = '';

		$config = $this->getServiceLocator()->get('Config');
		$staticSalt = $config['static_salt'];

		return $staticSalt;
	}

	public function getAdminEmail() {
		$email = '';
	
		$config = $this->getServiceLocator()->get('Config');
		$email = $config['admin_email'];
	
		return $email;
	}

	public function encriptPassword($staticSalt, $password, $dynamicSalt) {
		return $password = md5($staticSalt . $password . $dynamicSalt);
	}

	public function generatePassword($l = 8, $c = 0, $n = 0, $s = 0) {
		$count = $c + $n + $s;
		$out = '';

		if (!is_int($l) || !is_int($c) || !is_int($n) || !is_int($s)) {
			trigger_error('Argument(s) not an integer', E_USER_WARNING);

			return false;
		}
		elseif ($l < 0 || $l > 20 || $c < 0 || $n < 0 || $s < 0) {
			trigger_error('Argument(s) out of range', E_USER_WARNING);

			return false;
		}
		elseif ($c > $l) {
			trigger_error('Number of password capitals required exceeds password length', E_USER_WARNING);

			return false;
		}
		elseif ($n > $l) {
			trigger_error('Number of password numerals exceeds password length', E_USER_WARNING);

			return false;
		}
		elseif ($s > $l) {
			trigger_error('Number of password capitals exceeds password length', E_USER_WARNING);

			return false;
		}
		elseif ($count > $l) {
			trigger_error('Number of password special characters exceeds specified password length', E_USER_WARNING);

			return false;
		}
		// all inputs clean, proceed to build password
		// change these strings if you want to include or exclude possible password characters
		$chars = 'abcdefghijklmnopqrstuvwxyz';
		$caps = strtoupper($chars);
		$nums = '0123456789';
		$syms = '!@#$%^&*()-+?';

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

	public function sendConfirmationEmail($user) {
		$transport = $this->getServiceLocator()->get('mail.transport');
		$message = new Message();

		$this->getRequest()->getServer();

		$message->addTo($user->email)
			->addFrom($this->getAdminEmail())
			->setSubject('Palun kinnitage oma email!')
			->setBody('E-maili kinnitamiseks minge lehele: '
				. $this->getRequest()->getServer('SERVER_NAME') . $this->getRequest()->getServer('HTTP_ORIGIN')
				. $this->url()->fromRoute('register/confirm-email') . '/' . $user->registration_token
			);

		$transport->send($message);
	}

	public function sendPasswordByEmail($email, $password) {
		$transport = $this->getServiceLocator()->get('mail.transport');
		$message = new Message();

		$this->getRequest()->getServer();

		$message->addTo($email)
			->addFrom($this->getAdminEmail())
			->setSubject('Teie parool on muudetud!')
			->setBody('Teie parool lehelt on muudetud. Teie uus parool on: ' . $password);

		$transport->send($message);
	}

	public function getUserTable() {
		if (!$this->userTable) {
			$sm = $this->getServiceLocator();

			$this->userTable = $sm->get('EksamiKeskkond\Model\UserTable');
		}
		return $this->userTable;
	}
}