<?php

namespace EksamiKeskkond\Service;

use Zend\Mail\Message;

class EmailService {

	static function sendEmail($emailTo, $emailFrom, $subject, $body, $transport) {
		$message = new Message();

		$message->setEncoding('UTF-8')
			->addTo($emailTo)
			->addFrom($emailFrom)
			->setSubject($subject)
			->setBody($body);

		$transport->send($message);

		return;
	}
}