<?php
class CurrentTimeHandler extends AutoHandler {
	
	function handle(DataObject $model) {
		return 'now()';
	}

}
?>
