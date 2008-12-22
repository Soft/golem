<?php

/**
* Base class for all controllers
*/
abstract class Controller {
	
	public $AutoRender = true;
	public $DefaultAction = "Index";
	
	protected $view = null;
	protected $models = null;
	protected $arguments = array();
	
	private $actionName;
	
	final public function __construct() {
		if ($this->models === null) {
			$modelName = preg_replace("/^(.+)Controller$/", "$1Model", get_class($this), 1);
			$this->models = array($modelName);
		}
		
		$this->arguments = $_GET;
		if (isset($this->arguments["action"])) {
			$this->actionName = $this->arguments["action"];
		} else {
			$this->actionName = $this->DefaultAction;
		}
		
		if (!$this->actionExists($this->actionName)) {
			throw new Exception(
				sprintf(
					"Action '%s' doesn't exist in '%s' controller",
					$this->actionName,
					get_class($this)
				)
			);
		}
		
		unset($this->arguments["controller"], $this->arguments["action"]);
		
		$this->view = new View(
			get_class($this),
			$this->actionName
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
			if (!preg_match("/^\s*\*\s\+\s*(?P<method>GET|POST)\s+(?P<parameter>[^\s]+)\s+\\$$name\s*$/m", $comment, $match)) {
				throw new Exception("'$name' parameter for '$this->actionName' action isn't binded to GET or POST parameter.");
			}
			
			$source = ($match["method"] == "GET") ? $this->arguments : $_POST;
			
			if (isset($source[$match["parameter"]]) &&
				trim($source[$match["parameter"]]) != "") {
				$args[] = $source[$match["parameter"]];
			} else {
				if ($param->isDefaultValueAvailable()) {
					$args[] = $param->getDefaultValue();
				} else {
					throw new Exception(
						sprintf(
							"Action '%s' in '%s' controller requires '%s' as %s parameter.",
							$this->actionName,
							get_class($this),
							$match["parameter"],
							$match["method"]
						)
					);
				}
			}	
			
		}
		return $args;
	}
	
	private function actionExists() {
		$reflector = new ReflectionClass(get_class($this));
		if ($reflector->hasMethod($this->actionName)) {
			$method = $reflector->getMethod($this->actionName);
			$denied = array("BeforeAction", "AfterAction", "OnCreated", "Run");
			if ($method->isPublic() &&
				!preg_match("/^__.+$/", $this->actionName) &&
				!in_array($this->actionName, $denied)) {
				return true;
			}
		}
		return false;
	}
	
	private function getModelPath($modelName) {
		return APPDIR . DS . "Models". DS . $modelName . ".php";
	}
	
	public function OnCreated() {}
	public function BeforeAction() {}
	public function AfterAction() {}
	
}

