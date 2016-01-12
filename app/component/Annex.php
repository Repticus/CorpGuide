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
	public $edit = FALSE;
	public $upload = FALSE;

	function __construct(ActiveRow $annexRow) {
		parent::__construct();
		$this->id = $annexRow->id;
		$this->order = $annexRow->order;
		$this->title = $annexRow->title;
		$this->document = $annexRow->document ? $annexRow->document : NULL;
		$this->extension = $this->getFileExtension($this->document);
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
				if ($this->isSetDocument()) {
					return Html::el('a')
										 ->href($this->presenter->docDir . "/" . $this->document)
										 ->setHtml($this->getTitle());
				}
				return $this->getTitle();
		}
		return $this->edit ? $this->form[$field]->getControl() : $this->$field;
	}

	/**
	 * Sets given field value.
	 * @param string $field field name
	 * @param mixed $value field value
	 * @return void
	 */
	public function setField($field, $value = NULL) {
		switch ($field) {
			case "document":
				$value = $this->getDocName();
				$this->renameDocument($value);
				break;
			case "extension":
				$value = $this->getFileExtension($value);
				break;
		}
		$this->$field = $value;
	}

	/**
	 * Checks if document is set.
	 * @param void
	 * @return boolean
	 */
	protected function isSetDocument() {
		return $this->document ? TRUE : FALSE;
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
	 * Gets a file name for annex document.
	 * @param void
	 * @return void
	 */
	protected function getDocName() {
		return Strings::webalize($this->id . "_" . $this->getTitle(), '_', false) . "." . $this->extension;
	}

	/**
	 * Gets a extension from file name.
	 * @param  string $fileName full file name
	 * @return string|null Return file extension or null.
	 */
	public function getFileExtension($fileName) {
		$filepart = pathinfo($fileName);
		return isset($filepart['extension']) ? $filepart['extension'] : NULL;
	}

	/**
	 * Saves a uploaded document to storage directory.
	 * @param  object $file object of Nette\Http\FileUpload
	 * @return boolean return true if save was succesfull
	 */
	public function saveDocument(FileUpload $file) {
		$docdir = $this->presenter->docDir;
		if (!$docdir or ! $this->document) {
			$this->presenter->flashMessage('Dokument nelze uložit. Je potřeba nastavit parametr docDir a nazev dokumentu.', 'error');
			return FALSE;
		}
		$newFile = $docdir . "/" . $this->document;
		$file->move($newFile);
		return TRUE;
	}

	/**
	 * Deletes a document from storage directory.
	 * @param  void
	 * @return boolean return true if delete was succesfull
	 */
	public function deleteDocument() {
		$docdir = $this->presenter->docDir;
		if (!$docdir or ! $this->document) {
			$this->presenter->flashMessage('Dokument nelze vymazat. Je potřeba nastavit parametr docDir a nazev dokumentu.', 'error');
			return FALSE;
		}
		$oldFile = $docdir . "/" . $this->document;
		if (file_exists($oldFile)) {
			return unlink($oldFile);
		}
		return FALSE;
	}

	/**
	 * Renames a document in storage directory.
	 * @param string $name new name for document
	 * @return boolean return true if rename was succesfull
	 */
	public function renameDocument($name) {
		$docDir = $this->presenter->docDir;
		if (!$docDir or ! $this->document) {
			$this->presenter->flashMessage('Dokument nelze přejmenovat. Parametry docDir a název dokumentu musí být nastaveny.', 'error');
			return FALSE;
		}
		$oldName = $docDir . "/" . $this->document;
		$newName = $docDir . "/" . $name;
//		if (file_exists($oldName)) {
//		}
//		if (count($filepart) < 2) {
//			throw new DirectiveException(DirectiveException::BAD_FILE_EXTENSION, $fileName);
//		}
//		return $rename ? TRUE : FALSE;
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
		if ($extList) {
			$extError = 'Přípona dokumentu může být typu ' . implode(", ", $extList) . '.';
		} else {
			$extError = 'Je nutné definovat seznam povolených typů dokumentů. Nastavte parametr docExt v config.neon.';
		}
		$fileLimit = $this->presenter->fileLimit;
		$fileLimitText = $this->presenter->convertToUnits($fileLimit, 2);
		$form->addUpload('file', 'Dokument')
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
		$this->setField("title", $data->title);
		if ($this->isSetDocument()) {
			$this->setField("document");
		}
		$this->presenter->flashMessage('Data byla aktualizována.', 'success');
	}

	/**
	 * Process annex upload form.
	 * @param  void
	 */
	public function formUploadSave(Form $form) {
		$data = $form->getValues();
		$this->deleteDocument();
		$this->setField("extension", $data->file->name);
		$this->setField("document");
		$this->saveDocument($data->file);
		$this->presenter->flashMessage('Dokument byl aktualizován.', 'success');
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
	 * Checks if uploaded file extension is in allowed extensions list.
	 * @param  object $file object of Nette\Forms\Controls\UploadControl
	 * @param  array $extList list of allowed extensions without dots
	 * @return boolean return true if uploaded file extension is in allowed list
	 */
	public function checkFileExtension(UploadControl $file, $extList) {
		$fileExt = $this->getFileExtension($file->value->name);
		if ($fileExt && in_array($fileExt, $extList)) {
			return TRUE;
		}
		return FALSE;
	}

}
