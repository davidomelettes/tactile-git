<?php

/**
 * Base for Invoices
 * 
 * @author jas
 */
class Invoices {

	/**
	 * The account to interrogate
	 *
	 * @var OmeletteAccount
	 */
	protected $account;
	
	/**
	 * Constructor
	 * Takes a OmeletteAccount instance
	 * 
	 * @param OmeletteAccount $account
	 */
	function __construct(OmeletteAccount $account) {
		$this->account = $account;
	}
	
	function getInvoices() {
		$db = DB::Instance();
		$query = 'SELECT date_part(\'year\', p.created) AS year, date_part(\'month\', p.created) AS month, date_part(\'day\', p.created) AS day, p.id, p.xero_invoice_id, p.amount, p.description, p.xero_invoice_id FROM payment_records p, tactile_accounts a where p.invoiced=true and a.id=p.account_id AND a.site_address='.$db->qstr($this->account->site_address).' ORDER BY p.created DESC';
		return $db->GetAll($query);
	}
	
	function getAccountDetails() {
		$db = DB::Instance();
		$query = 'SELECT a.email, p.cost_per_month, a.account_expires + interval \'30 days\' AS repeat FROM tactile_accounts a, account_plans p where p.id=a.current_plan_id AND a.site_address='.$db->qstr($this->account->site_address);
		return $db->GetRow($query);
	}
}
