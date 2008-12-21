<?php

/**
* Base class for all controllers
*/
abstract class Controller {
	
	public $View = null;
	public $AutoRender = true;
	protected $models = null;
	
	final public function __construct() {
		if ($this->models === null) {
			$modelName = preg_replace("/^(.+)Controller$/", "$1Model", get_class($this), 1);
			$this->models = array($modelName);
		}
		$this->loadModels();
		$this->OnCreated();
	}
	
	final private function loadModels() {
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
	
	final private function getModelPath($modelName) {
		return APPDIR . DS . "Models". DS . $modelName . ".php";
	}
	
	public function OnCreated() {}
	public function BeforeAction() {}
	public function AfterAction() {}
	
}


