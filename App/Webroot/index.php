<?php

define("DS", DIRECTORY_SEPARATOR);
define("WEBROOT", realpath(dirname(__FILE__)));
define("GOLEMDIR", dirname(dirname(WEBROOT)) . DS . "Golem");

require_once(GOLEMDIR . DS . "Bootstrap.php");
