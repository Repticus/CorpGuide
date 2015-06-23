<?php

namespace App;

use Nette\Utils\Finder;

class CorporateDirective {

	public $data = array();
	public $errors = array();

	function __construct($docsDir) {
		$dirFiles = Finder::findFiles("?*.*")->in($docsDir);
		foreach ($dirFiles as $file) {
			$this->setDirectiveType($file, $docsDir);
		}
		ksort($this->data);
		foreach ($this->data as $dirnum => $dirdata) {
			if (!isset($dirdata["name"])) {
				unset($this->data[$dirnum]);
				foreach ($dirdata["annex"] as $anxnum => $anxdata) {
					try {
						throw new BadDirectiveException(6, $anxdata["file"]);
					} catch (BadDirectiveException $e) {
						$this->errors[] = $e->getMessage();
					}
				}
				continue;
			}
			ksort($this->data[$dirnum]["annex"]);
		}
	}

	private function setDirectiveType($file, $docsDir) {
		$fileName = iconv("ISO8859-2", "UTF-8", $file->getFilename());
		$filepart = explode("_", $fileName);
		$fileLink = $docsDir . $fileName;
		try {
			switch (count($filepart)) {
				case 3:
					$this->addAnnex($filepart[0], $filepart[1], $filepart[2], $fileLink, $fileName);
					break;
				case 5:
					$this->addDirective($filepart[0], $filepart[1], $filepart[2], $filepart[3], $filepart[4], $fileLink, $fileName);
					break;
				default:
					throw new BadDirectiveException(1, $fileName);
			}
		} catch (BadDirectiveException $e) {
			$this->errors[] = $e->getMessage();
		}
	}

	private function addDirective($directive, $date, $change, $revision, $name, $link, $file) {
		if (isset($this->data[$directive]["name"])) {
			throw new BadDirectiveException(5, $file);
		}
		$date = $this->setDate($date, $file);
		$name = $this->setName($name, $file);
		$record = array(
			 "date" => $date,
			 "change" => $change,
			 "revision" => $revision,
			 "name" => $name,
			 "link" => $link
		);
		if (isset($this->data[$directive])) {
			$this->data[$directive] = array_merge($record, $this->data[$directive]);
		} else {
			$this->data[$directive] = $record;
			$this->data[$directive]["annex"] = array();
		}
	}

	private function addAnnex($directive, $annex, $name, $link, $file) {
		$name = $this->setName($name, $file);
		if (isset($this->data[$directive]["annex"][$annex])) {
			throw new BadDirectiveException(4, $file);
		} else {
			$this->data[$directive]["annex"][$annex] = array(
				 "name" => $name,
				 "link" => $link,
				 "file" => $file
			);
		}
	}

	private function setDate($date, $fileName) {
		$date = strtotime($date);
		if (!$date) {
			throw new BadDirectiveException(3, $fileName);
		}
		return $date;
	}

	private function setName($name, $fileName) {
		$filepart = explode(".", $name);
		if (count($filepart) < 2) {
			throw new BadDirectiveException(2, $fileName);
		}
		array_pop($filepart);
		return implode(".", $filepart);
	}

}
