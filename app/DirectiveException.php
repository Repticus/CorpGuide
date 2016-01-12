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

	function __construct($code, $data = array()) {
		switch ($code) {
			case 1:
				$message = "Chybná hodnota parametru [docDir] v config.neon. Hodnota není nastavena.";
				break;
			case 2:
				$message = "Chybná hodnota parametru [docDir] v config.neon. Složka [$data[0]] neexistuje.";
				break;
			case 3:
				$message = "Na server byl odeslán příliš velký objem dat. Maximální velikost dat je [$data[0]].";
				break;
			case 4:
				$message = "Chybná hodnota parametru [post_max_size] v PHP.ini.";
				break;
			case 5:
				$message = "Chybná hodnota parametru [upload_max_filesize] v PHP.ini.";
				break;
			case 6:
				$message = "Chybná hodnota parametru [fileSize] v config.neon.";
				break;
			case 7:
				$message = "Hodnota [postLimit] v aplikaci nebyla nastavena.";
				break;
			default :
				$message = "Nedefinovaná chyba.";
		}
		parent::__construct($message, $code);
	}

	public function getFlashMessage() {
		return "[Chyba #" . $this->getCode() . "] " . $this->getMessage();
	}

}

//	const BAD_FILE_NAME = 1;
//	const BAD_FILE_EXTENSION = 2;
//	const BAD_DIRECTIVE_DATE = 3;
//	const BAD_ANNEX_NUMBER = 4;
//	const BAD_DIRECTIVE_NUMBER = 5;
//	const BAD_DIRECTIVE_LINK = 6;

//			case 1:
//				$message .= " Název souboru neodpovídá formátu směrnice ani přílohy.";
//				break;
//			case 2:
//				$message .= " Název souboru neobsahuje příponu - nelze určit typ dokumentu.";
//				break;
//			case 3:
//				$message .= " Datum schválení je chybné. Datum uvádějte ve tvaru <strong>den-měsíc-rok</strong>.";
//				break;
//			case 4:
//				$message .= " Číslo přílohy uvedené v názvu souboru pro danou směrnici již existuje.";
//				break;
//			case 5:
//				$message .= " Číslo směrnice uvedené v názvu souboru již existuje.";
//				break;
//			case 6:
//				$message .= " K této příloze nebyla nalezena směrnice s odpovídajícím číslem.";
//				break;
