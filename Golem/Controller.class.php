<?php

/**
* Base class for all controllers
*/
abstract class Controller {
	
	public $view = null;
	public $autoRender = true;
	
	public function beforeAction() {}
	public function afterAction() {}
	
}


