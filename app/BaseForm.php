<?php

namespace App;

use Nette\Application\UI\Form;

class BaseForm extends Form {

	public function __construct() {
		parent::__construct();
		$this->getRenderer()->wrappers['label']['suffix'] = ':';
	}

	/**
	 * Flash message error
	 */
	public function addError($message) {
		parent::addError($message);
		$this->parent->flashMessage($message, "error");
	}

}
