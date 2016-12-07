<?php

use Nette\Application\UI\Control;

class DirList extends Control {

	public $action = FALSE;

	function __construct($database) {
		parent::__construct();
		$dirData = $database->getDirectives();
		foreach ($dirData as $dirRow) {
			$directive = new Directive($dirRow);
			$this->addComponent($directive, "dir" . $dirRow->order);
		}
	}

	public function render() {
		$this->template->action = $this->action;
		$this->template->tools = $this->presenter->user->loggedIn;
		$this->template->render(__DIR__ . '/DirList.latte');
	}

}
