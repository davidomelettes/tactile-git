<?php

class ErrorController extends Controller {
	
	public function __construct($module, $view) {
		parent::__construct($module, $view);
		$this->view->set('layout', 'error');
	}

	public function not_found() {
		$this->view->setHeader('HTTP/1.0 404 Not Found');
		$this->view->set('head_title', 'Page Not Found');
	}
	
	public function error($e) {
		if (defined('PRODUCTION') && !PRODUCTION) {
			$this->view->set('exception', $e);
		}
	}
	
}
