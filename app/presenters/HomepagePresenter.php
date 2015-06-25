<?php

namespace App\Presenters;

use Nette,
	 App\BaseForm,
	 App\CorporateDirective;

class HomepagePresenter extends Nette\Application\UI\Presenter {

	public $docDir;

	public function startup() {
		parent::startup();
		$this->docDir = $this->context->parameters['docDir'];
	}

	public function renderDefault() {
		$directive = new CorporateDirective($this->docDir);
		foreach ($directive->errors as $error) {
			$this->flashMessage($error, 'error');
		}
		$this->template->directive = $directive->data;
	}

	protected function createComponentLogIn() {
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

	protected function createComponentUploadDocument() {
		$form = new BaseForm;
		$form->addMultiUpload('files', 'Směrnice');
		$form->addSubmit('login', 'Nahrát');
		$form->onSuccess[] = array($this, 'succeessUploadDocument');
		return $form;
	}

	public function succeessUploadDocument(BaseForm $form) {
		$files = $form['files']->getValue();
		foreach ($files as $file) {
			$fileName = iconv("UTF-8", "ISO8859-2", $file->name);
			$file->move($this->docDir . $fileName);
		}
		$this->flashMessage('Load form.', 'success');
		$this->redirect('Homepage:');
	}

	protected function createComponentLogOut() {
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
