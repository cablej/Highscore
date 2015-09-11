<?php

require_once('Bridge.php');

define("SCORE_LIMIT", 10);

$bridge = Bridge::getInstance();

$highscore = new Highscore();

$bridge->appendActionHandler($highscore);
$bridge->appendInputHandler($highscore);

class Highscore {
	function handleAction($action, $mysqli) {
		$bridge = Bridge::getInstance();
		switch ($action) {
			case "GetScores":
				$input = $bridge->validateInputs([["name" => "limit", "type" => "NATURAL_NUMBER", "required" => "false"]]);
				
				$limit = isSet($input["limit"]) ? $input["limit"]: SCORE_LIMIT;
				
				$bridge->appendReturnValue($this->getScores($bridge, $mysqli, $limit));
				return true;
				break;
			case "UploadScore":
				$input = $bridge->validateInputs([["name" => "score", "type" => "NATURAL_NUMBER", "required" => "true"], ["name" => "username", "type" => "USERNAME", "required" => "true"]]);
				
				$username = $input["username"];
				$sql = "SELECT `score` FROM `Highscores` WHERE `username` = '$username'";
				$result = $bridge->query($sql, $mysqli);
				$currentHighscore = isSet($result["score"]) ? $result["score"] : -1;
				$newScore = $input["score"];
				if($newScore > $currentHighscore) {
					$sql = "INSERT INTO `Highscores`(`username`, `score`) VALUES ('$username','$newScore') ON DUPLICATE KEY UPDATE score = $newScore";
					$result = $bridge->query($sql, $mysqli);
				}
				
				$bridge->appendReturnValue($this->getScores($bridge, $mysqli));
				return true;
				break;
		}
	}
	
	function getScores($bridge, $mysqli, $limit = SCORE_LIMIT) {
		$sql = "SELECT `username`, `score` FROM `Highscores` ORDER BY `score` DESC LIMIT $limit";
		return $bridge->query($sql, $mysqli);
	}
	
	function validateInput($input, $type) {
		$bridge = Bridge::getInstance();
		switch ($type) {
			case "NATURAL_NUMBER":
				if(is_numeric($input) && floatval($input) >= 0) {
					return floatval($input);
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