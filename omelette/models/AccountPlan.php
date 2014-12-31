<?php
class AccountPlan extends DataObject {
	
	public function __construct() {
		parent::__construct('account_plans');
		$this->validateUniquenessOf('name');
		
		$this->getField('user_limit')->setFormatter(new ReplacementFormatter(0,'Unlimited'));
		$this->getField('contact_limit')->setFormatter(new ReplacementFormatter(0,'Unlimited'));
		$this->getField('file_space')->setFormatter(new ReplacementFormatter(0,'Unlimited', new FilesizeFormatter()));
		$this->getField('opportunity_limit')->setFormatter(new ReplacementFormatter(0,'Unlimited'));
	}
	
	/**
	 * Returns true iff the cost_per_month of the plan is 0 (zero)
	 *
	 * @return Boolean
	 */
	public function is_free() {
		return $this->cost_per_month == '0';
	}
	
	public function is_per_user() {
		return $this->per_user == 't';
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getId() {
		return $this->id;
	}
}
