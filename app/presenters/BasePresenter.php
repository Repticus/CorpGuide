<?php

namespace App\Presenters;

use DirList,
	 Exception,
	 DirectiveException,
	 Nette\Database\Context,
	 Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter {

	/** @var string directory where documents are stored */
	public $docDir;

	/** @var integer limit in bytes for single uploaded file */
	public $fileLimit;

	/** @var integer limit in bytes for all data sent by post method */
	public $postLimit;

	/** @var object Nette\Database\Context */
	private $database;

	public function __construct(Context $database) {
		parent::__construct();
		$this->database = $database;
	}

	public function startup() {
		parent::startup();
		$this->setDocDir();
		$this->setPostLimit();
		$this->checkPostLimit();
		$this->setFileLimit();
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
		try {
			$docDir = $this->context->parameters['docDir'];
			if (!$docDir) {
				throw new DirectiveException(DirectiveException::DOCDIR_NOT_SET);
			}
			$path = ROOT . "/" . $docDir;
			if (!is_dir($path)) {
				throw new DirectiveException(DirectiveException::DOCDIR_NOT_EXISTS, array($path));
			}
			$this->docDir = $docDir;
		} catch (DirectiveException $e) {
			$this->docDir = FALSE;
			$this->flashMessage($e->getFlashMessage(), 'error');
		}
	}

	/**
	 * Sets a data size limit in bytes for post method.
	 * @param void
	 * @return void
	 */
	private function setPostLimit() {
		try {
			$postLimit = $this->convertToBytes(ini_get('post_max_size'));
			if (!$postLimit) {
				throw new DirectiveException(DirectiveException::PHP_POST_SIZE_NOT_SET);
			}
			$memLimit = $this->convertToBytes(ini_get('memory_limit'));
			$this->postLimit = min($postLimit, $memLimit);
		} catch (DirectiveException $e) {
			$this->postLimit = FALSE;
			$this->flashMessage($e->getFlashMessage(), 'error');
		}
	}

	/**
	 * Sets a file size limit in bytes for uploaded files.
	 * @param void
	 * @return void
	 */
	private function setFileLimit() {
		try {
			if (!$this->postLimit) {
				throw new DirectiveException(DirectiveException::POST_LIMIT_NOT_SET);
			}
			$phpLimit = $this->convertToBytes(ini_get('upload_max_filesize'));
			if (!$phpLimit) {
				throw new DirectiveException(DirectiveException::PHP_FILE_SIZE_NOT_SET);
			}
			$appLimit = $this->context->parameters['fileSize'];
			if ($appLimit) {
				$appLimit = $this->convertToBytes($appLimit);
				if (!$appLimit) {
					throw new DirectiveException(DirectiveException::FILE_LIMIT_NOT_SET);
				}
				$this->fileLimit = min($phpLimit, $appLimit, $this->postLimit);
			} else {
				$this->fileLimit = min($phpLimit, $this->postLimit);
			}
		} catch (DirectiveException $e) {
			$this->fileLimit = FALSE;
			$this->flashMessage($e->getFlashMessage(), 'error');
		}
	}

	/**
	 * Check if data sent to server does not exceed maximum allowed limit.
	 * @param void
	 * @return boolean return true if limit was not exceeded.
	 */
	private function checkPostLimit() {
		try {
			$postData = (int) filter_input(INPUT_SERVER, 'CONTENT_LENGTH', FILTER_SANITIZE_SPECIAL_CHARS);
			if ($postData > $this->postLimit) {
				$limit = $this->convertToUnits($this->postLimit);
				throw new DirectiveException(DirectiveException::PHP_POST_SIZE_EXCEEDED, array($limit));
			}
			return TRUE;
		} catch (DirectiveException $e) {
			$this->flashMessage($e->getMessage(), 'error');
			return FALSE;
		}
	}

	/**
	 * Convert numeric value with optional unit symbol into number of bytes without units. (K, M, G)
	 * @param string $value text value with or without unit symbol
	 * @return integer numeric value in bytes without unit symbol
	 */
	public function convertToBytes($value) {
		$number = (int) $value;
		$unit = strtolower(substr($value, -1));
		switch ($unit) {
			case 'g': $number *= 1024;
			case 'm': $number *= 1024;
			case 'k': $number *= 1024;
		}
		return $number;
	}

	/**
	 * Convert number of bytes into number with data units. (B, kB, MB, GB, TB)
	 * @param integer $value numeric value in bytes
	 * @param integer $digits [optional] number of decimal digits to cut down
	 * @return string converted number complemented with units.
	 */
	public function convertToUnits($value, $digits = NULL) {
		if (!(is_int($value) or is_float($value)) or $value < 0) {
			throw new Exception("Parameter [number] must be a integer or float equal or bigger than zero.");
		}
		$units = array(" B", " kB", " MB", " GB", " TB");
		foreach ($units as $unit) {
			if ($value < 1024) {
				if (isset($digits)) {
					$value = $this->cutDecimalNumber($value, $digits);
				}
				return $value . $unit;
			}
			$value /= 1024;
		}
	}

	/**
	 * Cut down decimal number on given number of digits.
	 * @param integer|float $number number to cut down
	 * @param integer $digits number of decimal digits to cut down
	 * @return integer|float
	 */
	public function cutDecimalNumber($number, $digits) {
		if (is_int($number)) {
			return $number;
		}
		if (!is_int($digits) or $digits < 0) {
			throw new Exception("Parameter [digits] must be a integer equal or bigger than zero.");
		}
		$part = explode(".", $number);
		$part[1] = substr($part[1], 0, $digits);
		if (!(int) $part[1]) {
			unset($part[1]);
		}
		return implode(".", $part);
	}

}
