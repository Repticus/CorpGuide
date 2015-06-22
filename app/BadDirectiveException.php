<?php

namespace App;

use Exception;

class BadDirectiveException extends Exception {

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
				$message = "Nedefinovaná chyba";
		}
		parent::__construct($message, $code, NULL);
	}

}
