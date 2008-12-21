<?php

error_reporting(E_ALL | E_STRICT);

require_once("Router.class.php");

require_once("Model.class.php");
require_once("View.class.php");
require_once("Controller.class.php");

$router = new Router();
$router->Route();

