<?php
class DefaultOwnerHandler extends AutoHandler {
	protected $default_username;
	public function __construct($onupdate=false,$username) {
		parent::__construct($onupdate);
		$this->default_username=$username;
	}

	function handle(DataObject $model) {
		return $this->default_username;
	}
}
?>