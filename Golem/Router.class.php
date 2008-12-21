<?php

/**
* Class for finding and executing the right controller
*/
class Router {
	
	public $DefaultController = "IndexController";
	public $DefaultAction = "Index";
	private $controller = null;
	
	public function __construct() {
		$this->Arguments = $_GET;
	}
	
	public function Route() {
		$this->SelectController();
		$this->ExecuteAction();
	}
	
	public function SelectController() {
		if (isset($this->Arguments["controller"])) {
			$controllerName = $this->Arguments["controller"] . "Controller";
		} else {
			$controllerName = $this->DefaultController;
		}
		
		if ($this->ControllerExists($controllerName)) {
			require_once($this->getControllerPath($controllerName));
			if (class_exists($controllerName)) {
				$controller = new $controllerName();
				if (is_subclass_of($controller, "Controller")) {
					$this->controller = $controller;
				} else {
					throw new Exception("Controllers must be derived from Controller class.");
				}
			} else {
				throw new Exception(
						sprintf(
							"File %s doesn't contain definition for %s controller.",
							basename($this->getControllerPath($controllerName)),
							$controllerName
						)
					);
			}
		} else {
			throw new Exception("Cannot find {$this->DefaultController} controller.");
		}
	}
	
	public function ExecuteAction() {
		if ($this->controller) {
			if (isset($this->Arguments["action"])) {
				$action = $this->Arguments["action"];
			} else {
				$action = $this->DefaultAction;
			}
			if ($this->ActionExists($action)) {
				$this->controller->$action();
			} else {
				throw new Exception(
						sprintf(
								"Action %s doesn't exist in %s controller",
								$action,
								get_class($this->controller)
							)
					);
			}
		}
	}
	
	public function ControllerExists($name) {
		return file_exists($this->getControllerPath($name));
	}
	
	public function ActionExists($actionName) {
		if ($this->controller) {
			if (method_exists($this->controller, $actionName) &&
				!preg_match("/^__.+$/", $actionName)) {
				return true;
			}
		}
		return false;
	}
	
	private function getControllerPath($name) {
		return APPDIR . DS . "Controllers" . DS . $name . ".php";
	}
	
}

