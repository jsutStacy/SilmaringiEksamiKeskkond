<?php

return array(
	'bankLinkPreferences' => array(
		'test' => array(
			'my_private_key' => 'banklinks/my_private_key.pem',
			'my_private_key_password' => '',
			'bank_certificate' => 'banklinks/bank_certificate.pem',
			'my_id' => 'uid516316',
			'account_number' => 'EE171010123456789017',
			'account_owner' => 'PANGAKONTO OMANIK',
			'bankname' => 'test',
		),
	),
	'bankPreferences' => array(
			'test' => array(
					'url' => 'https://pangalink.net/banklink/seb-common',
					'charset_parameter' => 'VK_ENCODING',
					'charset' => 'UTF-8',
			),
	),
	'VK_variableOrder' => array(
		1001 => array(
			'VK_SERVICE','VK_VERSION','VK_SND_ID',
			'VK_STAMP','VK_AMOUNT','VK_CURR',
			'VK_ACC','VK_NAME','VK_REF','VK_MSG'
		),
		1011 => array(
			'VK_SERVICE','VK_VERSION','VK_SND_ID',
			'VK_STAMP','VK_AMOUNT','VK_CURR',
			'VK_ACC','VK_NAME','VK_REF','VK_MSG',
			'VK_RETURN', 'VK_CANCEL', 'VK_DATETIME'
		),
		1101 => array(
			'VK_SERVICE','VK_VERSION','VK_SND_ID',
			'VK_REC_ID','VK_STAMP','VK_T_NO','VK_AMOUNT','VK_CURR',
			'VK_REC_ACC','VK_REC_NAME','VK_SND_ACC','VK_SND_NAME',
			'VK_REF','VK_MSG','VK_T_DATE'
		),
		1901 => array(
			'VK_SERVICE','VK_VERSION','VK_SND_ID',
			'VK_REC_ID','VK_STAMP','VK_REF','VK_MSG'
		),
		1911 => array(
			'VK_SERVICE','VK_VERSION','VK_SND_ID',
			'VK_REC_ID','VK_STAMP','VK_REF','VK_MSG'
		),
		1111 => array(
			'VK_SERVICE','VK_VERSION','VK_SND_ID',
			'VK_REC_ID','VK_STAMP','VK_T_NO','VK_AMOUNT','VK_CURR',
			'VK_REC_ACC','VK_REC_NAME','VK_SND_ACC','VK_SND_NAME',
			'VK_REF','VK_MSG','VK_T_DATETIME'
		),
	),
);

?>