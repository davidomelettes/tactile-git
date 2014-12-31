<?php
class UserCompanyHandler extends AutoHandler {

	function handle(DataObject $model) {
		return EGS::getCompanyId();
	}

}
?>
