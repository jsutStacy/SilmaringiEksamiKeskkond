<?php

namespace EksamiKeskkondTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase {

	protected $traceError = true;

	public function setUp() {
		$this->setApplicationConfig(
			include 'config' . DIRECTORY_SEPARATOR . 'application.config.php'
		);
		parent::setUp();
	}

	public function testIndexAction() {
		$this->dispatch('/');
		$this->assertResponseStatusCode(200);
	
		$this->assertModuleName('EksamiKeskkond');
		$this->assertControllerName('IndexController');
		$this->assertControllerClass('IndexController');
		$this->assertMatchedRouteName('home');
	}

	public function testAdminLoginAction() {
		$postData = array(
			'email' => 'admin@silmaring.ee',
			'password' => 'Admin1',
		);
		$this->dispatch('/login', 'POST', $postData);
		$this->assertResponseStatusCode(302);
	
		$this->assertRedirectTo('/admin');
	}

	public function testTeacherLoginAction() {
		$postData = array(
			'email' => 'opetaja1@silmaring.ee',
			'password' => 'Õpetaja1',
		);
		$this->dispatch('/login', 'POST', $postData);
		$this->assertResponseStatusCode(302);
	
		$this->assertRedirectTo('/teacher');
	}

	public function testStudentLoginAction() {
		$postData = array(
			'email' => 'opilane1@silmaring.ee',
			'password' => 'Õpilane1',
		);
		$this->dispatch('/login', 'POST', $postData);
		$this->assertResponseStatusCode(302);

		$this->assertRedirectTo('/student');
	}

	public function testLoginAction() {
		$this->dispatch('/login');
		$this->assertResponseStatusCode(200);

		$this->assertModuleName('EksamiKeskkond');
		$this->assertControllerName('IndexController');
		$this->assertControllerClass('IndexController');
		$this->assertMatchedRouteName('home/login');
	}
}