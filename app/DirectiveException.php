<?php

namespace App;

use Exception;

class DirectiveException extends Exception {

	const BAD_FILE_NAME = 1;
	const BAD_FILE_EXTENSION = 2;
	const BAD_DIRECTIVE_DATE = 3;
	const BAD_ANNEX_NUMBER = 4;
	const BAD_DIRECTIVE_NUMBER = 5;
	const BAD_DIRECTIVE_LINK = 6;

	function __construct($code, $filename) {
		$message = "Soubor <strong>$filename</strong> byl vyřazen ze seznamu směrnic.";
		switch ($code) {
			case 1:
				$message .= " Název souboru neodpovídá formátu směrnice ani přílohy.";
				break;
			case 2:
				$message .= " Název souboru neobsahuje příponu - nelze určit typ dokumentu.";
				break;
			case 3:
				$message .= " Datum schválení je chybné. Datum uvádějte ve tvaru <strong>den-měsíc-rok</strong>.";
				break;
			case 4:
				$message .= " Číslo přílohy uvedené v názvu souboru pro danou směrnici již existuje.";
				break;
			case 5:
				$message .= " Číslo směrnice uvedené v názvu souboru již existuje.";
				break;
			case 6:
				$message .= " K této příloze nebyla nalezena směrnice s odpovídajícím číslem.";
				break;
			default:
				$message = "Nedefinovaná chyba.";
		}
		parent::__construct($message, $code);
	}

}
