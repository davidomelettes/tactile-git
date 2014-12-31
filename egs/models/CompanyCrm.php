<?php
debug_print_backtrace();exit;
if(file_exists(MODEL_ROOT.'CompanyLookups.php'))
	require_once MODEL_ROOT.'CompanyLookups.php';
else if(file_exists(CORE_MODEL_ROOT.'CompanyLookups.php'))
	require_once CORE_MODEL_ROOT.'CompanyLookups.php';
	
class CompanyCrm extends DataObject {

	function __construct() {
		parent::__construct('company_crm');
		$this->belongsTo('AccountStatus','account_status_id','account_status');
		$this->belongsTo('CompanyClassification','classification_id','company_classification');
		$this->belongsTo('CompanyIndustry','industry_id','company_industry');
		$this->belongsTo('CompanyRating','rating_id','company_rating');
		$this->belongsTo('CompanySource','source_id','company_source');
		$this->belongsTo('CompanyStatus','status_id','company_status');
		$this->belongsTo('CompanyType','type_id','company_type');
	}	
	
}
?>