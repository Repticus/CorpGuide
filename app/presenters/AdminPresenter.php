<?php

namespace App\Presenters;

use DirectiveException,
	 Nette\Utils\Html;

class AdminPresenter extends BasePresenter {

	public $docExt;

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

}
