<?php

/**
 * Base for usage checking
 * 
 * @author gj
 */
abstract class UsageCheckerAbstract {

	/**
	 * The account to interrogate
	 *
	 * @var OmeletteAccount
	 */
	protected $account;
	
	/**
	 * The usage, once calculated
	 *
	 * @var String
	 */
	protected $usage;
	
	/**
	 * Constructor
	 * Takes a OmeletteAccount instance
	 * 
	 * @param OmeletteAccount $account
	 */
	function __construct(OmeletteAccount $account) {
		$this->account = $account;
	}
	
	public function getFormattedUsage() {
		return $this->getUsage();
	}
	
	public function getUsage() {
		if(!isset($this->usage)) {
			$this->usage = $this->calculateUsage();		
		}
		return $this->usage;
	}
	
	abstract protected function calculateUsage();
}

?>
