<?php

use Nette\Application\UI\Control;

class DirList extends Control {

	public $action = FALSE;

	function __construct($database) {
		parent::__construct();
		$drvData = $database->getAllDrv();
		foreach ($drvData as $drvRow) {
			$directive = new Directive($drvRow, $drvRow->number);
			$this->addComponent($directive, "dir" . $drvRow->id);
		}
	}

	public function render() {
		$this->template->action = $this->action;
		$this->template->tools = $this->presenter->user->loggedIn;
		$this->template->render(__DIR__ . '/DirList.latte');
	}

}
