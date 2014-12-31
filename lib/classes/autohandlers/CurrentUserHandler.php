<?php
class CurrentUserHandler extends AutoHandler {
	
	function handle(DataObject $model) {
		try {
			return EGS::getUsername();
		}
		catch(Exception $e) {
			return false;
		}
	}
}
?>