<?php
namespace EksamiKeskkond\Form;
use Zend\Form\Form;

Class BanklinkForm extends Form {
	
	public function __construct($name = null) {
		
		// we want to ignore the name passed
		parent::__construct('EksamiKeskkond');
		
		$this->setAttributes(array(
			'method' => 'post',
		));
		
		$this->add(array(
			'name' => 'VK_SERVICE',
			'value' => '1011',
			'attributes' => array(
				'type' => 'hidden',
			),
		));
		
		$this->add(array(
			'name' => 'VK_VERSION',
			'value' => '008',
			'attributes' => array(
				'type' => 'hidden',
			),
		));
		
		$this->add(array(
			'name' => 'VK_SND_ID',
			'attributes' => array(
				'type' => 'hidden',
			),
		));
		
		$this->add(array(
			'name' => 'VK_STAMP',
			'attributes' => array(
				'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'VK_AMOUNT',
			'attributes' => array(
				'type' => 'hidden',
			),
		));
		
		$this->add(array(
			'name' => 'VK_CURR',
			'attributes' => array(
				'type' => 'hidden',
			),
		));
		

		$this->add(array(
			'name' => 'VK_ACC',
			'attributes' => array(
					'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'VK_NAME',
			'attributes' => array(
					'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'VK_REF',
			'value' => '',
			'attributes' => array(
					'type' => 'hidden',
			),
		));

		$this->add(array(
				'name' => 'VK_LANG',
				'value' => 'EST',
				'attributes' => array(
						'type' => 'hidden',
				),
		));

		$this->add(array(
			'name' => 'VK_MSG',
			'attributes' => array(
					'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'VK_RETURN',
			'attributes' => array(
					'type' => 'hidden',
			),
		));

		$this->add(array(
				'name' => 'VK_CANCEL',
				'attributes' => array(
						'type' => 'hidden',
				),
		));

		$this->add(array(
				'name' => 'VK_DATETIME',
				'attributes' => array(
						'type' => 'hidden',
				),
		));

		$this->add(array(
				'name' => 'VK_ENCODING',
				'attributes' => array(
						'type' => 'hidden',
				),
		));

		$this->add(array(
				'name' => 'VK_MAC',
				'attributes' => array(
						'type' => 'hidden',
				),
		));

		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'id' => 'bankLinkSubmit',
				'style' => 'display:none'
			),
		));

	}
}