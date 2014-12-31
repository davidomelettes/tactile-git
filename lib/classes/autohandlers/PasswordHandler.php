<?php
class PasswordHandler extends AutoHandler {
	
	function handle(DataObject $model) {
		return md5($model->password);

	}

}
?>
