<?php

/**
* Class for finding and executing the right controller
*/
class Router {
	
	public $DefaultController = "IndexController";
	
	private $controller = null;
	private $controllerName;
	private $arguments;
	
	public function __construct() {
		$this->arguments = $_GET;
		if (isset($this->arguments["controller"])) {
			$this->controllerName = $this->arguments["controller"] . "Controller";
		} else {
			$this->controllerName = $this->DefaultController;
		}		
	}
	
	public function Route() {
		$this->initializeController();
		$this->controller->Run();
	}
	
	private function initializeController() {
		if ($this->ControllerExists($this->controllerName)) {
			require_once($this->getControllerPath($this->controllerName));
			if (class_exists($this->controllerName)) {
				$controller = new $this->controllerName();
				if (is_subclass_of($controller, "Controller")) {
					$this->controller = $controller;
				} else {
					throw new Exception("Controllers must be derived from Controller class.");
				}
				
			} else {
				throw new Exception(
					sprintf(
						"File '%s' doesn't contain definition for '%s' controller.",
						basename($this->getControllerPath($this->controllerName)),
						$this->controllerName
					)
				);
			}
		} else {
			throw new Exception("Cannot find '$this->controllerName' controller.");
		}
	}
	
	public function ControllerExists($name) {
		return file_exists($this->getControllerPath($name));
	}
	
	private function getControllerPath($name) {
		return APPDIR . DS . "Controllers" . DS . $name . ".php";
	}
	
}

