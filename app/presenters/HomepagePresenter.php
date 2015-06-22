<?php

namespace App\Presenters;

use Nette,
	 App\BaseForm,
	 App\CorporateDirective;

class HomepagePresenter extends Nette\Application\UI\Presenter {

	public function renderDefault() {
		$directive = new CorporateDirective;
		foreach ($directive->errors as $error) {
			$this->flashMessage($error, 'error');
		}
		$this->template->directive = $directive->data;
	}

	protected function createComponentLoginForm() {
		$form = new BaseForm;
		$form->addText('user', 'Login');
		$form->addPassword('password', 'Heslo');
		$form->addSubmit('login', 'Přihlásit');
		$form->onSuccess[] = array($this, 'succeessLogin');
		return $form;
	}

	public function succeessLogin(BaseForm $form) {
		try {
			$this->getUser()
					  ->setExpiration(0, TRUE)
					  ->login($form['user']->value, $form['password']->value);
			$this->flashMessage('Byl jste úspěšně přihlášen.', 'success');
			$this->redirect('Homepage:');
		} catch (Nette\Security\AuthenticationException $e) {
			$this->flashMessage('Špatný login nebo heslo.', 'error');
		}
	}

	protected function createComponentLoadForm() {
		$form = new BaseForm;
		$form->addUpload('files', 'Směrnice');
		$form->addSubmit('login', 'Nahrát');
		$form->onSuccess[] = array($this, 'succeessLoad');
		return $form;
	}

	public function succeessLoad(BaseForm $form) {
		$file = $form['files']->getValue();
		$fileUrl = $this->context->parameters['docsDir'] . $file->name;
		$file->move($fileUrl);
		$this->flashMessage('Load form.', 'success');
		$this->redirect('Homepage:');
	}

	protected function createComponentLogOutForm() {
		$form = new BaseForm;
		$form->addSubmit('logout', 'Odhlásit');
		$form->onSuccess[] = array($this, 'succeessLogOut');
		return $form;
	}

	public function succeessLogOut() {
		$this->getUser()->logout(FALSE);
		$this->flashMessage('Byl jste úspěšně odhlášen.', 'success');
		$this->redirect('Homepage:');
	}

}
