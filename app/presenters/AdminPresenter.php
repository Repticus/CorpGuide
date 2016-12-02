<?php

namespace App\Presenters;

use DirectiveException,
	 Nette\Utils\Html;

class AdminPresenter extends BasePresenter {

	public $docExt;

	/** @var integer limit in bytes for single uploaded file */
	public $fileLimit;

	/** @var integer limit in bytes for all data sent by post method */
	public $postLimit;

	public function startup() {
		parent::startup();
		if (!$this->user->loggedIn) {
			$this->flashMessage('Zabezpečená sekce. Přihlašte se prosím.', 'error');
			$this->redirect('Public:directives');
		}
		$this->docExt = $this->context->parameters['docExt'];
		$this->setPostLimit();
		$this->setFileLimit();
		$this->checkPostLimit();
	}

	/**
	 * Process logout action.
	 * @param  void
	 * @return void
	 */
	public function actionLogOut() {
		$this->getUser()->logout(FALSE);
		$this->flashMessage('Odhlášení proběhlo úspěšně.', 'success');
		$this->redirect('Public:directives');
	}

	/**
	 * Returns a logout button control element.
	 * @param  void
	 * @return object Nette\Utils\Html
	 */
	public function showLogoutButton() {
		$link = "parent.location='" . $this->link('logOut') . "'";
		$element = Html::el('input')->addAttributes(array(
			 'type' => 'button',
			 'value' => 'Odhlásit',
			 'onClick' => $link
		));
		return $element;
	}

	/**
	 * Sets a data size limit in bytes for post method.
	 * @param void
	 * @return void
	 */
	private function setPostLimit() {
		$postLimit = $this->convertToBytes(ini_get('post_max_size'));
		if (!$postLimit) {
			throw new DirectiveException(DirectiveException::PHP_POST_SIZE_NOT_SET);
		}
		$memLimit = $this->convertToBytes(ini_get('memory_limit'));
		$this->postLimit = min($postLimit, $memLimit);
	}

	/**
	 * Sets a file size limit in bytes for uploaded files.
	 * @param void
	 * @return void
	 */
	private function setFileLimit() {
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
	}

	/**
	 * Check if data sent to server does not exceed maximum allowed limit.
	 * @param void
	 * @return boolean return true if limit was not exceeded.
	 */
	private function checkPostLimit() {
		if (!$this->postLimit) {
			throw new DirectiveException(DirectiveException::POST_LIMIT_NOT_SET);
		}
		$postData = (int) filter_input(INPUT_SERVER, 'CONTENT_LENGTH', FILTER_SANITIZE_SPECIAL_CHARS);
		if ($postData > $this->postLimit) {
			$limit = $this->convertToUnits($this->postLimit);
			throw new DirectiveException(DirectiveException::PHP_POST_SIZE_EXCEEDED, array($limit));
		}
		return TRUE;
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
