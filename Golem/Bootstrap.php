<?php

error_reporting(E_ALL | E_STRICT);
if (!version_compare(PHP_VERSION, "5.1.0", ">")) {
	trigger_error("At least PHP 5.1.0 is required to run Golem. Installed PHP version is " . PHP_VERSION);
	exit();
}

require_once("Router.class.php");

require_once("Model.class.php");
require_once("View.class.php");
require_once("Controller.class.php");

$router = new Router();
$router->Route();

