<?php
/**
 * Validate a DO as a whole, rather than individual fields
 * I'm not sure whether this should be an abstract class, as I would think test() will have to be fairly specific?
 *although as it's
 */
abstract class DataObjectValidator implements iTestable {

	abstract public function test(DataObject $do);
	
}
?>