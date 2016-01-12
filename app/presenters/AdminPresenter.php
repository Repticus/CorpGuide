<?php

namespace App\Presenters;

use Nette\Utils\Html;

class AdminPresenter extends BasePresenter {

	public $docExt;

	public function startup() {
		parent::startup();
		if (!$this->user->loggedIn) {
			$this->flashMessage('Zabezpečená sekce. Přihlašte se prosím.', 'error');
			$this->redirect('Public:directives');
		}
		$this->docExt = $this->context->parameters['docExt'];
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

}
