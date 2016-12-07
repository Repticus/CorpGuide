<?php

namespace App\Presenters;

use DirList,
	 DirRepository,
	 DirectiveException,
	 Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter {

	/** @var string directory where documents are stored */
	public $docDir;

	/** @var DirRepository */
	private $database;

	public function __construct(DirRepository $database) {
		parent::__construct();
		$this->database = $database;
	}

	public function startup() {
		parent::startup();
		$this->setDocDir();
	}

	/**
	 * Creates a DirList component.
	 * @param  void
	 * @return object Nette\Application\UI\Form
	 */
	protected function createComponentDirList() {
		return new DirList($this->database);
	}

	/**
	 * Sets a directory where documents are stored.
	 * @param void
	 * @return void
	 */
	private function setDocDir() {
		$docDir = $this->context->parameters['docDir'];
		if (!$docDir) {
			throw new DirectiveException(DirectiveException::DOCDIR_NOT_SET);
		}
		$path = ROOT . "/" . $docDir;
		if (!is_dir($path)) {
			throw new DirectiveException(DirectiveException::DOCDIR_NOT_EXISTS, array($path));
		}
		$this->docDir = $docDir;
	}

}
