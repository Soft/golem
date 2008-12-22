<?php

/**
* Class for finding and executing the right controller.
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
	
	/**
	 * Finds the right controller and executes it.
	 */
	public function Route() {
		$this->initializeController();
		$this->controller->Run();
	}
	
	private function initializeController() {
		if ($this->controllerExists()) {
			require_once($this->getControllerPath());
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
						basename($this->getControllerPath()),
						$this->controllerName
					)
				);
			}
		} else {
			throw new Exception("Cannot find '$this->controllerName' controller.");
		}
	}
	
	private function controllerExists() {
		return file_exists($this->getControllerPath($this->controllerName));
	}
	
	private function getControllerPath() {
		return APPDIR . DS . "Controllers" . DS . $this->controllerName . ".php";
	}
	
}

