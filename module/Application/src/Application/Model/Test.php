<?php

namespace Application\Model;

class Test {

	public $id;
	public $content;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->content = (isset($data['content'])) ? $data['content'] : null;
	}
}