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
						"File '%s' doesn't contain definition for '%s' controller.",
						basename($this->getControllerPath($controllerName)),
						$controllerName
					)
				);
			}
		} else {
			throw new Exception("Cannot find '$controllerName' controller.");
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
				$this->controller->View = new View(
						get_class($this->controller),
						$action
					);
				$this->controller->BeforeAction();
				
				$args = $this->parseActionParameters($action);
				call_user_func_array(array(&$this->controller, $action), $args);
				
				$this->controller->AfterAction();
				if ($this->controller->AutoRender) {
					$this->controller->View->Render();
				}
			} else {
				throw new Exception(
						sprintf(
								"Action '%s' doesn't exist in '%s' controller",
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
			$denied = array("BeforeAction", "AfterAction");
			if (method_exists($this->controller, $actionName) &&
				!preg_match("/^__.+$/", $actionName) &&
				!in_array($actionName, $denied)) {
				return true;
			}
		}
		return false;
	}
	
	private function parseActionParameters($action) {
		$reflector = new ReflectionMethod(
			get_class($this->controller),
			$action
		);
		$params = $reflector->getParameters();
		$comment = $reflector->getDocComment();
		
		$args = array();
		foreach ($params as $param) {
			$name = $param->getName();
			if (!preg_match("/^\s*\*\s\+\s*GET\s+([^\s]+)\s+\\$$name\s*$/m", $comment, $match)) {
				throw new Exception("'$name' parameter for '$action' action isn't binded to GET parameter.");
			}
			if (isset($this->Arguments[$match[1]])) {
				$args[] = $this->Arguments[$match[1]];
			} else {
				if ($param->isDefaultValueAvailable()) {
					$args[] = $param->getDefaultValue();
				} else {
					throw new Exception(
						sprintf(
							"Action '%s' in '%s' controller requires '%s' as GET parameter.",
							$action,
							get_class($this->controller),
							$match[1]
						)
					);
				}
			}
			
		}
		return $args;
	}
	
	private function getControllerPath($name) {
		return APPDIR . DS . "Controllers" . DS . $name . ".php";
	}
	
}

