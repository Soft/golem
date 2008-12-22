<?php

/**
* Class for finding and executing the right controller
*/
class Router {
	
	public $DefaultController = "IndexController";
	public $DefaultAction = "Index";
	
	private $controller = null;
	private $controllerName;
	private $actionName;
	
	public function __construct() {
		$this->Arguments = $_GET;
		
		if (isset($this->Arguments["controller"])) {
			$this->controllerName = $this->Arguments["controller"] . "Controller";
		} else {
			$this->controllerName = $this->DefaultController;
		}
		if (isset($this->Arguments["action"])) {
			$this->actionName = $this->Arguments["action"];
		} else {
			$this->actionName = $this->DefaultAction;
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
				
				if ($this->actionExists($this->actionName)) {
					$controller = new $this->controllerName($this->actionName);
				} else {
					throw new Exception(
						sprintf(
								"Action '%s' doesn't exist in '%s' controller",
								$this->actionName,
								$this->controllerName
							)
					);
				}
				
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
	
	private function actionExists($actionName) {
		$reflector = new ReflectionClass($this->controllerName);
		if ($reflector->hasMethod($actionName)) {
			$method = $reflector->getMethod($actionName);
			$denied = array("BeforeAction", "AfterAction");
			if ($method->isPublic() &&
				!preg_match("/^__.+$/", $actionName) &&
				!in_array($actionName, $denied)) {
				return true;
			}
		}
		return false;
	}
	
	private function getControllerPath($name) {
		return APPDIR . DS . "Controllers" . DS . $name . ".php";
	}
	
}

