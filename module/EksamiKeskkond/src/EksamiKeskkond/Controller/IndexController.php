<?php

namespace EksamiKeskkond\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;

use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;

use EksamiKeskkond\Form\LoginForm;
use EksamiKeskkond\Filter\LoginFilter;
use EksamiKeskkond\Model\User;

class IndexController extends AbstractActionController {

	public function indexAction() {
		return new ViewModel();
	}

	public function loginAction() {
		$user = $this->identity();
		$form = new LoginForm();
		$messages = null;

		$request = $this->getRequest();

		if ($request->isPost()) {
			$form->setInputFilter(new LoginFilter($this->getServiceLocator()));
			$form->setData($request->getPost());
			
			if ($form->isValid()) {
				$data = $form->getData();
				$sm = $this->getServiceLocator();
				$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
				$config = $this->getServiceLocator()->get('Config');
				$staticSalt = $config['static_salt'];

				$authAdapter = new AuthAdapter($dbAdapter,
					'user',
					'email',
					'password',
					"MD5(CONCAT('$staticSalt', ?, password_salt)) AND status = 1"
				);
				$authAdapter
					->setIdentity($data['email'])
					->setCredential($data['password']);

				$auth = new AuthenticationService();

				$result = $auth->authenticate($authAdapter);

				switch ($result->getCode()) {

					case Result::FAILURE_IDENTITY_NOT_FOUND:
						// do stuff for nonexistent identity
						break;

					case Result::FAILURE_CREDENTIAL_INVALID:
						// do stuff for invalid credential
						break;

					case Result::SUCCESS:
						$storage = $auth->getStorage();
						$storage->write($authAdapter->getResultRowObject(null, 'password'));
						$time = 1209600;

						if ($data['remember_me']) {
							$sessionManager = new \Zend\Session\SessionManager();
							$sessionManager->rememberMe($time);
						}
						return $this->redirect()->toRoute('home');
						break;

					default:
						// do stuff for other failure
						break;
				}

				foreach ($result->getMessages() as $message) {
					$messages .= "$message\n";
				}
			}
		}
		return new ViewModel(array('form' => $form, 'messages' => $messages));
	}

	public function logoutAction() {
		$auth = new AuthenticationService();

		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
		}
		$auth->clearIdentity();

		$sessionManager = new \Zend\Session\SessionManager();
		$sessionManager->forgetMe();

		return $this->redirect()->toRoute('home');
	}
}