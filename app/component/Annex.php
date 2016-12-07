<?php

use Nette\Application\UI\Form,
	 Nette\Application\UI\Control,
	 Nette\Database\Table\ActiveRow,
	 Nette\Forms\Controls\UploadControl,
	 Nette\Http\FileUpload,
	 Nette\Utils\Strings,
	 Nette\Utils\Html;

class Annex extends Control {

	public $id;
	public $order;
	public $title;
	public $document;
	public $extension;
	public $form;
	public $row;
	public $edit = FALSE;
	public $upload = FALSE;

	function __construct(ActiveRow $annexRow) {
		parent::__construct();
		$this->id = $annexRow->id;
		$this->order = $annexRow->order;
		$this->title = $annexRow->title;
		$this->document = $annexRow->document ? $annexRow->document : NULL;
		$this->row = $annexRow;
	}

	public function handleEdit() {
		$this->edit = TRUE;
		$this->form = $this["formEdit"];
		$this->presenter['dirList']->action = $this->form;
	}

	public function handleUpload() {
		$this->upload = TRUE;
		$this->form = $this["formUpload"];
		$this->presenter['dirList']->action = $this->form;
	}

	/**
	 * Shows a edit tools depending on edit mode.
	 * @param void
	 * @return void
	 */
	public function showTools() {
		if ($this->edit or $this->upload) {
			echo $this->form["save"]->getControl();
			echo $this->showStornoButton();
		} else {
			echo $this->showEditButton();
			echo $this->showUploadButton();
		}
	}

	/**
	 * Creates a edit button control element.
	 * @param void
	 * @return object Nette\Utils\Html
	 */
	public function showEditButton() {
		$link = $this->link('edit!');
		return $this->createButton("E", $link);
	}

	/**
	 * Creates a upload button control element.
	 * @param void
	 * @return object Nette\Utils\Html
	 */
	public function showUploadButton() {
		$link = $this->link('upload!');
		return $this->createButton("U", $link);
	}

	/**
	 * Creates a storno button control element.
	 * @param void
	 * @return object Nette\Utils\Html
	 */
	public function showStornoButton() {
		$link = $this->presenter->link('directives');
		return $this->createButton("C", $link);
	}

	/**
	 * Creates button element.
	 * @param string $value button value
	 * @param string $link button link
	 * @return object Nette\Utils\Html
	 */
	public function createButton($value, $link) {
		$element = Html::el('input')->addAttributes(array(
			 'type' => 'button',
			 'value' => $value,
			 'onClick' => "parent.location='$link'"
		));
		return $element;
	}

	/**
	 * Returns given field control element depending on edit or upload mode.
	 * @param string $field name of field
	 * @return object|string object of Nette\Utils\Html or string value
	 */
	public function showField($field) {
		switch ($field) {
			case "title":
				if ($this->edit) {
					return $this->form["title"]->getControl();
				}
				if ($this->upload) {
					return $this->form["file"]->getControl();
				}
				if ($this->document) {
					return Html::el('a')
										 ->addAttributes(array('target' => '_blank'))
										 ->href($this->presenter->docDir . "/" . $this->document)
										 ->setHtml($this->getTitle());
				}
				return $this->getTitle();
		}
		return $this->edit ? $this->form[$field]->getControl() : $this->$field;
	}

	/**
	 * Gets a title for annex.
	 * @param void
	 * @return string title for annex
	 */
	protected function getTitle() {
		return "Příloha " . $this->order . " " . $this->title;
	}

	/**
	 * Gets a file name part for annex document without extension.
	 * @param void
	 * @return string.
	 */
	protected function getDocName() {
		return Strings::webalize($this->id . "_" . $this->getTitle(), '_', false);
	}

	/**
	 * Gets a extension from file name.
	 * @param  string $fileName full file name
	 * @return string|NULL Returns file extension or NULL if file has no extension.
	 */
	public function getDocExtension($fileName) {
		$filepart = pathinfo($fileName);
		if (!isset($filepart['extension'])) {
			return NULL;
		}
		return $filepart['extension'];
	}

	/**
	 * Sets full file name for annex document. If $extension is not set, it will be obtained from the $document property.
	 * @param string $extension file extension
	 * @return string Return file name.
	 */
	protected function setDocName($extension = NULL) {
		if (!$extension) {
			if (!$this->document) {
				throw new DirectiveException(DirectiveException::DOCUMENT_NOT_SET);
			}
			$extension = $this->getDocExtension($this->document);
		}
		$this->document = $this->getDocName() . "." . $extension;
	}

	/**
	 * Creates a edit form component for annex.
	 * @param  void
	 * @return object Nette\Application\UI\Form
	 */
	protected function createComponentFormEdit() {
		$form = new Form;
		$form->addText('title', 'Název směrnice', 50)
				  ->setValue($this->title)
				  ->setRequired('Pole %label musí být vyplněno.');
		$form->addSubmit('save', 'S');
		$form->onSuccess[] = array($this, 'formEditSave');
		$form->onError[] = array($this, 'formError');
		return $form;
	}

	/**
	 * Creates a upload form component.
	 * @param  void
	 * @return object Nette\Application\UI\Form
	 */
	protected function createComponentFormUpload() {
		$form = new Form;
		$extList = $this->presenter->docExt;
		$extError = 'Přípona dokumentu může být typu ' . implode(", ", $extList) . '.';
		$fileLimit = $this->presenter->fileLimit;
		$fileLimitText = $this->presenter->convertToUnits($fileLimit, 2);
		$form->addUpload('file', 'Dokument')
				  ->setRequired('Pole %label musí být vyplněno.')
				  ->addRule(array($this, 'checkFileExtension'), $extError, $extList)
				  ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru je ' . $fileLimitText . '.', $fileLimit);
		$form->addSubmit('save', 'S');
		$form->onSuccess[] = array($this, 'formUploadSave');
		$form->onError[] = array($this, 'formError');
		return $form;
	}

	/**
	 * Process annex edit form.
	 * @param  void
	 */
	public function formEditSave(Form $form) {
		$data = $form->getValues();
		$this->title = $data->title;
		if ($this->document) {
			$oldName = $this->document;
			$this->setDocName();
			$this->renameDocFile($oldName, $this->document);
		}
		$this->row->update(array('title' => $this->title, 'document' => $this->document));
		$this->presenter->flashMessage('Příloha směrnice byla aktualizována.', 'success');
		$this->redirect("this");
	}

	/**
	 * Process annex upload form.
	 * @param  void
	 */
	public function formUploadSave(Form $form) {
		$data = $form->getValues();
		if ($this->document) {
			$this->deleteDocFile();
		}
		$extension = $this->getDocExtension($data->file->name);
		$this->setDocName($extension);
		$this->uploadDocFile($data->file);
		$this->row->update(array('document' => $this->document));
		$this->presenter->flashMessage('Příloha směrnice byla aktualizována.', 'success');
		$this->redirect("this");
	}

	/**
	 * Process form if error occurs.
	 * @param  void
	 */
	public function formError(Form $form) {
		$errors = $form->getErrors();
		foreach ($errors as $error) {
			$this->presenter->flashMessage($error, "error");
		}
		switch ($form->name) {
			case "formEdit":
				$this->handleEdit();
				break;
			case "formUpload":
				$this->handleUpload();
				break;
		}
	}

	/**
	 * Moves a uploaded document file to storage directory.
	 * @param  object $file object of Nette\Http\FileUpload
	 * @return void
	 */
	public function uploadDocFile(FileUpload $file) {
		$docdir = $this->presenter->docDir;
		if (!$this->document) {
			throw new DirectiveException(DirectiveException::DOCUMENT_NOT_SET);
		}
		$newFile = $docdir . "/" . $this->document;
		$file->move($newFile);
	}

	/**
	 * Renames a document file in storage directory.
	 * @param string $oldName old document name
	 * @param string $newName new document name
	 * @return void
	 */
	public function renameDocFile($oldName, $newName) {
		$docDir = $this->presenter->docDir;
		$oldName = $docDir . "/" . $oldName;
		$newName = $docDir . "/" . $newName;
		rename($oldName, $newName);
	}

	/**
	 * Deletes a document file from storage directory.
	 * @param  void
	 * @return void
	 */
	public function deleteDocFile() {
		$docdir = $this->presenter->docDir;
		if (!$this->document) {
			throw new DirectiveException(DirectiveException::DOCUMENT_NOT_SET);
		}
		$file = $docdir . "/" . $this->document;
		if (file_exists($file)) {
			unlink($file);
		}
	}

	/**
	 * Checks if uploaded file extension is in allowed extensions list.
	 * @param  object $file object of Nette\Forms\Controls\UploadControl
	 * @param  array $extList list of allowed extensions without dots
	 * @return boolean return true if uploaded file extension is in allowed list
	 */
	public function checkFileExtension(UploadControl $file, $extList) {
		$fileExt = $this->getDocExtension($file->value->name);
		if (in_array($fileExt, $extList)) {
			return TRUE;
		}
		return FALSE;
	}

}
