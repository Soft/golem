<?php

class View {
	
	private $values = array();
	
	public function __construct($controller, $action) {
		if (file_exists($this->getViewPath($controller, $action))) {
			$this->Path = $this->getViewPath($controller, $action);
		} else {
			throw new Exception("Cannot find view for $action in controller $controller.");
		}
	}
	
	public function Set($key, $value) {
		$this->values[$key] = $value;
	}
	
	public function Render() {
		var_export($this->values);
		require($this->Path);
	}
	
	private function getViewPath($controller, $action) {
		return APPDIR . DS . "Views" . DS . $controller . DS . $action . ".view";
	}
	
}

