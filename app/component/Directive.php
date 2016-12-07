<?php

use Nette\Application\UI\Form,
	 Nette\Forms\Controls\TextInput,
	 Nette\Database\Table\ActiveRow,
	 Nette\Utils\Strings;

class Directive extends Annex {

	public $date;
	public $change;
	public $revision;

	function __construct(ActiveRow $dirRow) {
		parent::__construct($dirRow);
		$this->date = $dirRow->date->format('d.m.Y');
		$this->change = $dirRow->change;
		$this->revision = $dirRow->revision;
		$this->addAnnex($dirRow);
	}

	/**
	 * Returns given field value or control element if field is in edit mode.
	 * @param string $field name of field
	 * @return string|object
	 */
	public function showField($field) {
		switch ($field) {
			case "order":
				return $this->$field;
		}
		return parent::showField($field);
	}

	/**
	 * Gets a title for directive.
	 * @return string Title for directive.
	 */
	protected function getTitle() {
		return $this->title;
	}

	/**
	 * Gets a file name for uploaded directive document.
	 * @param void
	 * @return void
	 */
	protected function setDocName($extension = NULL) {
		return Strings::webalize($this->id . "_" . $this->title, '_', false) . "." . $this->extension;
	}

	/**
	 * Adds all annexes for this directive.
	 * @param object $dirRow annex data Nette\Database\Table\ActiveRow
	 * @return void
	 */
	public function addAnnex(ActiveRow $dirRow) {
		$annexData = $dirRow->related('annex.id')->order('order');
		foreach ($annexData as $annexRow) {
			$annex = new Annex($annexRow);
			$this->addComponent($annex, "annex" . $annexRow->order);
		}
	}

	/**
	 * Gets a annex number for this directive.
	 * @param void
	 * @return void
	 */
	public function getAnnexCount() {
		return $this->getComponents(FALSE, "Annex")->count();
	}

	/**
	 * Creates a edit form component directive.
	 * @param  void
	 * @return object Nette\Application\UI\Form
	 */
	protected function createComponentFormEdit() {
		$form = parent::createComponentFormEdit();
		$form->addText('id', 'Číslo směrnice', 7)
				  ->setValue($this->id)
				  ->setRequired('%label musí být vyplněno.');
		$form->addText('date', 'Datum schválení', 8)
				  ->setValue($this->date)
				  ->addRule(array($this, 'checkValidDate'), '%label musí být platné datum.')
				  ->setRequired('%label musí být vyplněno.');
		$form->addText('change', 'Číslo změny', 3)
				  ->setValue($this->change)
				  ->setRequired('%label musí být vyplněno.')
				  ->addRule(Form::INTEGER, 'Číslo změny musí být celé číslo.')
				  ->addRule(Form::MIN, 'Číslo změny musí být kladné číslo.', 0);
		$form->addText('revision', 'Číslo revize', 3)
				  ->setValue($this->revision)
				  ->setRequired('%label% musí být vyplněno.')
				  ->addRule(Form::INTEGER, 'Číslo revize musí být celé číslo.')
				  ->addRule(Form::MIN, 'Číslo revize musí být kladné číslo.', 0);
		return $form;
	}

	/**
	 * Process a directive edit form.
	 * @param  void
	 */
	public function formEditSave(Form $form) {
		$data = $form->getValues();
		$this->setField("id", $data->id);
		$this->setField("title", $data->title);
		$this->setField("date", $data->date);
		$this->setField("change", $data->change);
		$this->setField("revision", $data->revision);
		if ($this->isSetDocument()) {
			$this->setField("document");
		}
		$this->presenter->flashMessage('Data byla aktualizována.', 'success');
	}

	/**
	 * Checks if given date is valid.
	 * @param  object $textInput object of Nette\Forms\Controls\TextInput
	 * @return boolean return true if date is valid
	 */
	public function checkValidDate(TextInput $textInput) {
		$date = $textInput->value;
		if (preg_match("~^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}$~", $date)) {
			$date = explode('.', $date);
			$year = ($date[2] >= 1970 && $date[2] < 2038) ? $date[2] : 0;
			return checkdate($date[1], $date[0], $year);
		}
		return FALSE;
	}

}
