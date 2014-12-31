<?php
class CurrentWebsiteHandler extends AutoHandler {
	protected $website_id;
	function __construct($onupdate=false,$website_id) {
		$this->website_id=$website_id;
	}

	function handle(DataObject $model) {
		return $this->website_id;
	}
}
?>