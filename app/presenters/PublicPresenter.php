<?php

namespace App\Presenters;

use Nette\Application\UI\Form,
	 Nette\Security\AuthenticationException;

class PublicPresenter extends BasePresenter {

	public function startup() {
		parent::startup();
		if ($this->user->loggedIn) {
			$this->redirect('Admin:directives');
		}
	}

	/**
	 * Creates a login form component.
	 * @param void
	 * @return object Nette\Application\UI\Form
	 */
	protected function createComponentLogIn() {
		$form = new Form;
		$form->addText('user', 'Login');
		$form->addPassword('password', 'Heslo');
		$form->addSubmit('login', 'Přihlásit');
		$form->onSuccess[] = array($this, 'succeessLogin');
		return $form;
	}

	/**
	 * Process login form.
	 * @param void
	 * @return void
	 */
	public function succeessLogin(Form $form) {
		try {
			$this->getUser()
					  ->setExpiration(0, TRUE)
					  ->login($form['user']->value, $form['password']->value);
			$this->flashMessage('Přihlášení proběhlo úspěšně.', 'success');
			$this->redirect('Admin:directives');
		} catch (AuthenticationException $e) {
			$this->flashMessage('Přihlášení se nezdařilo. Špatný login nebo heslo.', 'error');
		}
	}

}
