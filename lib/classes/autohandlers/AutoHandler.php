<?php
abstract class AutoHandler {
	private $onupdate;
	
	public function __construct($onupdate=false) {
		$this->onupdate=$onupdate;
	}

	abstract public function handle(DataObject $model);
	
}
?>