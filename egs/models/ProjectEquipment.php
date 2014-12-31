<?php
class ProjectEquipment extends DataObject {
	public function __construct() {
		parent::__construct('project_equipment');
		
		
		$this->getField('red')->setFormatter(new PercentageFormatter());
		$this->getField('amber')->setFormatter(new PercentageFormatter());
		$this->getField('green')->setFormatter(new PercentageFormatter());
		
		$this->getField('hourly_cost')->setFormatter(new PriceFormatter());
		$this->getField('setup_cost')->setFormatter(new PriceFormatter());
		
		$this->setEnum('usable_hours', array('168'=>'24/7', '120'=>'24/5', '40'=>'Working Hours'));
	}
}
?>