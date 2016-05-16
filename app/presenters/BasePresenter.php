<?php

namespace App\Presenters;

use DirList,
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
			throw new DirectiveException(DirectiveException::CONVERT_UNITS_BAD_VALUE);
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
			throw new DirectiveException(DirectiveException::CONVERT_UNITS_BAD_DIGITS);
		}
		$part = explode(".", $number);
		$part[1] = substr($part[1], 0, $digits);
		if (!(int) $part[1]) {
			unset($part[1]);
		}
		return implode(".", $part);
	}

}
