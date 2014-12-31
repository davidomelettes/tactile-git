<?php
/**
 * Responsible for turning fieldnames and similar things into pretty human-readable words
 * @author gj
 */
class Prettifier implements Translation {
	
	/**
	 * An array of acronyms, for which ucfirst() isn't sensible
	 * @access protected
	 * @var Array $acronyms
	 */
	protected $acronyms=array(
		'crm'=>'CRM',
		'erp'=>'ERP',
		'dob'=>'DOB',
		'ni'=>'NI',
		'url'=>'URL',
		'ecommerce'=>'eCommerce',
		'accounts/erp'=>'Accounts/ERP',
		'erp setup'=>'ERP Setup',
		'vat_number'=>'VAT Number'
	);
		
	/**
	 * An array of words (db-fieldnames) which aren't formatted properly, and need special treatment
	 * @access protected
	 * @var Array $over_ride
	 */
	protected $over_ride=array(
		'companyaddresses'=>'company_addresses',
		'companycontactmethods'=>'company_contact_methods',
		'websiteadmins'=>'website_admins',
		'webpagecategories'=>'webpage_categories',
		'webpagerevisions'=>'webpage_revisions',
		'systemcompanies'=>'system_companies',
		'countrycode'=>'country',
		'lastupdated'=>'last_updated',
		'startdate'=>'start_date',
		'enddate'=>'end_date',
		'accountnumber'=>'account_number',
		'creditlimit'=>'credit_limit',
		'vatnumber'=>'vat_number',
		'companynumber'=>'company_number',
		'usercompanyaccesses'=>'user_company_access',
		'websitefiles'=>'website_files',
		'fullname'=>'full_name',
		'intranetsection'=>'intranet_section',
		'intranetpage'=>'intranet_page',
		'websiteadmin'=>'website_admin',
		'calendarevent'=>'calendar_event'
	);
	
	/**
	 * Takes a string, translates it into something human, and returns it
	 * 
	 * @param String $string
	 * @return String
	 */
	function translate($string) {
		if(isset($this->acronyms[strtolower($string)])) {
			return $this->acronyms[strtolower($string)];
		}
		if(isset($this->over_ride[strtolower($string)])) {
			return prettify($this->over_ride[strtolower($string)]);
		}
		return ucwords(str_replace('_',' ',str_replace('_id','',$string)));
	}

}
?>