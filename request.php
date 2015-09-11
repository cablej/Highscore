<?php

require_once('Bridge.php');

$bridge = Bridge::getInstance();

$bridge->processAction();

$bridge->returnCurrentValue();

?>