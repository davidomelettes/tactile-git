<?php
require_once(dirname(__FILE__) . '/../locator.php');

class AnyOldThing {
}

class WhenInstatiatingClasses extends UnitTestCase {

    function testCorrectClassIsInstantiated() {
		$locator = new Locator('AnyOldThing');
		$this->assertIsa($locator->instantiate(array()), 'AnyOldThing');
    }

	function testInstancesAreClones() {
		$locator = new Locator('AnyOldThing');
		$this->assertClone(
				$locator->instantiate(array()),
				$locator->instantiate(array()));
	}
}

class WhenInstatiatingSingletons extends UnitTestCase {

    function setUp() {
        Singleton::clear();
    }

    function testLocatorCanCreateClass() {
		$locator = new Singleton('AnyOldThing');
		$this->assertIsa($locator->instantiate(array()), 'AnyOldThing');
    }

	function testOnlyOneInstanceCreated() {
		$locator = new Singleton('AnyOldThing');
		$this->assertReference(
				$locator->instantiate(array()),
				$locator->instantiate(array()));
	}
}
?>