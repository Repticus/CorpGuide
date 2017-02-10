<?php

namespace App\Presenters;

use DirList,
	 DrvRepository,
	 AnxRepository,
	 DirectiveException,
	 Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter {

	/** @var string directory where documents are stored */
	public $docDir;

	/** @var DrvRepository */
	public $database;

	/** @var AnxRepository */
	public $anx;

	public function __construct(DrvRepository $database, AnxRepository $anx) {
		parent::__construct();
		$this->database = $database;
		$this->anx = $anx;
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
