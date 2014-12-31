<?php

class TestOfImportController extends ControllerTest {

	function setup() {
		parent::setup();
		$this->setDefaultLogin();
	}

	function testAccessToImportPage() {
		$this->setURL('import');
		
		$this->app->go();
		
		$this->genericPageTest();
	}
}
