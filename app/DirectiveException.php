<?php

//namespace App;

use \Exception;

class DirectiveException extends Exception {

	const DOCDIR_NOT_SET = 1;
	const DOCDIR_NOT_EXISTS = 2;
	const PHP_POST_SIZE_EXCEEDED = 3;
	const PHP_POST_SIZE_NOT_SET = 4;
	const PHP_FILE_SIZE_NOT_SET = 5;
	const FILE_LIMIT_NOT_SET = 6;
	const POST_LIMIT_NOT_SET = 7;
	const CONVERT_UNITS_BAD_VALUE = 8;
	const CONVERT_UNITS_BAD_DIGITS = 9;

	function __construct($code, $data = array()) {
		$message = $this->getDirectiveMessage($code, $data);
		parent::__construct($message, $code);
	}

	public function getDirectiveMessage($code, $data) {
		switch ($code) {
			case 1: return "Chybná hodnota parametru [docDir] v config.neon. Hodnota není nastavena.";
			case 2: return "Chybná hodnota parametru [docDir] v config.neon. Složka [$data[0]] neexistuje.";
			case 3: return "Na server byl odeslán příliš velký objem dat. Maximální velikost dat je [$data[0]].";
			case 4: return "Chybná hodnota parametru [post_max_size] v PHP.ini.";
			case 5: return "Chybná hodnota parametru [upload_max_filesize] v PHP.ini.";
			case 6: return "Chybná hodnota parametru [fileSize] v config.neon.";
			case 7: return "Hodnota [postLimit] v aplikaci nebyla nastavena.";
			case 8: return "Chybná hodnota parametru [value]. Hodnota musí být typu integer nebo float a musí být rovna nebo větší než nula.";
			case 9: return "Chybná hodnota parametru [digits]. Hodnota musí být typu integer a musí být rovna nebo větší než nula.";
			default : return "Nedefinovaná chyba.";
		}
	}

	public function getFlashMessage() {
		return "[Chyba #" . $this->getCode() . "] " . $this->getMessage();
	}

}
