<?php

namespace EksamiKeskkondTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AdminControllerTest extends AbstractHttpControllerTestCase {

	protected $traceError = true;

	public function setUp() {
		$this->setApplicationConfig(
			include 'config' . DIRECTORY_SEPARATOR . 'application.config.php'
		);
		parent::setUp();
	}

	public function testIndexAction() {
		$this->dispatch('/admin');
		$this->assertResponseStatusCode(302);

		$this->assertRedirectTo('/admin/courses');
	}
}