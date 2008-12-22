<?php

/**
* Base class for all controllers
*/
abstract class Controller {
	
	public $AutoRender = true;
	
	protected $view = null;
	protected $models = null;
	protected $arguments = array();
	
	private $actionName;
	
	final public function __construct($actionName) {
		if ($this->models === null) {
			$modelName = preg_replace("/^(.+)Controller$/", "$1Model", get_class($this), 1);
			$this->models = array($modelName);
		}
		$this->arguments = $_GET;
		if (isset($this->arguments["controller"])) {
			unset($this->arguments["controller"]);
		}
		if (isset($this->arguments["action"])) {
			unset($this->arguments["action"]);
		}
		$this->actionName = $actionName;
		$this->view = new View(
			get_class($this),
			$actionName
		);
		$this->loadModels();
		$this->OnCreated();
	}
	
	private function loadModels() {
		if (is_array($this->models)) {
			foreach ($this->models as $modelName) {
				if (file_exists($this->getModelPath($modelName))) {
					require_once($this->getModelPath($modelName));
					if (class_exists($modelName)) {
						$model = new $modelName();
						if (is_subclass_of($model, "Model")) {
							$this->$modelName = $model;
						} else {
							throw new Exception("Models must be derived from Model class.");
						}
					} else {
						throw new Exception(
							sprintf(
								"File %s doesn't contain definition for %s model.",
								basename($this->getModelPath($modelName)),
								$modelName
							)
						);
					}
				} else {
					throw new Exception("Cannot find $modelName model.");
				}
			}
		}
	}
	
	public function Run() {
		$args = $this->parseActionParameters();
		
		$this->BeforeAction();
		call_user_func_array(array(&$this, $this->actionName), $args);
		$this->AfterAction();
		
		if ($this->AutoRender) {
			$this->view->Render();
		}
	}
	
	private function parseActionParameters() {
		$reflector = new ReflectionMethod(
			get_class($this),
			$this->actionName
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
							$this->controllerName,
							$match[1]
						)
					);
				}
			}
			
		}
		return $args;
	}
	
	public function GetView() {
		return $this->view;
	}
	
	private function getModelPath($modelName) {
		return APPDIR . DS . "Models". DS . $modelName . ".php";
	}
	
	public function OnCreated() {}
	public function BeforeAction() {}
	public function AfterAction() {}
	
}


