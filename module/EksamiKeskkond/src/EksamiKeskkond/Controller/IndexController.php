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

				$authAdapter = new AuthAdapter($dbAdapter,
					'user',
					'email',
					'password',
					"MD5(?) AND status = 1"
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

						$user = $auth->getIdentity();

						switch ($user->role_id) {
							case 1 :
								return $this->redirect()->toRoute('admin');
								break;

							case 2 :
								return $this->redirect()->toRoute('teacher');
								break;

							case 3 :
								return $this->redirect()->toRoute('student');
								break;

							default :
								return $this->redirect()->toRoute('home');
								break;
						}
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