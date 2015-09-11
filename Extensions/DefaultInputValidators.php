<?php

require_once('Bridge.php');

$bridge = Bridge::getInstance();

$validators = new DefaultInputValidators();

$bridge->appendInputHandler($validators);

class DefaultInputValidators {
	
	function validateInput($input, $type) {
		$bridge = Bridge::getInstance();
		switch ($type) {
			case "NATURAL_NUMBER":
				if(is_numeric($input) && intval($input) >= 0) {
					return intval($input);
				} else {
					return NULL;
				}
				break;
			case "USERNAME":
				$ck_username = '/^[A-Za-z0-9_]{2,20}$/';
				if(preg_match($ck_username, $input)) {
					return $input;
				} else {
					return NULL;
				}
				break;
		}
	}
}

?>