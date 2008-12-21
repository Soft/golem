<?php

/**
* Base class for all controllers
*/
abstract class Controller {
	
	public $View = null;
	public $AutoRender = true;
	
	public function beforeAction() {}
	public function afterAction() {}
	
}


