<?php

use Nette\Application\UI\Form,
	 Nette\Forms\Controls\TextInput,
	 Nette\Database\Table\ActiveRow;

class Directive extends Annex {

	public $order;
	public $date;
	public $change;
	public $revision;

	function __construct(ActiveRow $drvRow) {
		parent::__construct($drvRow);
		$this->number = $drvRow->number;
		$this->date = $drvRow->date->format('d.m.Y');
		$this->change = $drvRow->change;
		$this->revision = $drvRow->revision;
		$this->addAnnex($drvRow);
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
	 * Adds all annexes for this directive.
	 * @param object $drvRow annex data Nette\Database\Table\ActiveRow
	 * @return void
	 */
	public function addAnnex(ActiveRow $drvRow) {
		$anxData = $drvRow->related('anx.drv_id')->order('number');
		foreach ($anxData as $anxRow) {
			$annex = new Annex($anxRow);
			$annex->directive = $this->number;
			$this->addComponent($annex, "anx" . $anxRow->id);
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
		$this->number = $data->number;
		$this->title = $data->title;
		$this->date = $data->date;
		$this->change = $data->change;
		$this->revision = $data->revision;
		$oldName = $this->document;
		$this->getDocName();
		try {
			$this->row->update(array(
				 'number' => $this->number,
				 'title' => $this->title,
				 'date' => $this->date,
				 'change' => $this->change,
				 'revision' => $this->revision,
				 'document' => $this->document
			));
			if ($oldName) {
				$this->renameDocFile($oldName, $this->document);
			}
//			foreach ($this->getComponents(FALSE, "Annex") as $annex) {
//				$annex->id = $this->id;
//				$annex->updateData();
//			}
			$this->presenter->flashMessage('Směrnice byla aktualizována.', 'success');
			$this->redirect('this');
		} catch (Nette\Application\AbortException $e) {
			throw $e;
		} catch (Exception $ex) {
			if ($ex->getCode() == 23000) {
				$this->presenter->flashMessage('Zadané číslo směrnice již existuje. ', 'error');
				$this->handleEdit();
			}
		}
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
