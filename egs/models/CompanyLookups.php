<?php

class AccountStatus extends DataObject {
	function __construct() {
		parent::__construct('account_statuses');
	}
}
class AccountStatusCollection extends DataObjectCollection {
	function __construct() {
		parent::__construct('AccountStatus');
	}
}



class CompanyClassification extends DataObject {
	function __construct() {
		parent::__construct('company_classifications');
	}
}
class CompanyClassificationCollection extends DataObjectCollection {
	function __construct() {
		parent::__construct('CompanyClassification');
	}
}



class CompanyIndustry extends DataObject {
	function __construct() {
		parent::__construct('company_industries');
	}
}
class CompanyIndustryCollection extends DataObjectCollection {
	function __construct() {
		parent::__construct('CompanyIndustry');
	}
}


class CompanyRating extends DataObject {
	function __construct() {
		parent::__construct('company_ratings');
	}
}
class CompanyRatingCollection extends DataObjectCollection {
	function __construct() {
		parent::__construct('CompanyRating');
	}
}



class CompanySource extends DataObject {
	function __construct() {
		parent::__construct('company_sources');
	}
}
class CompanySourceCollection extends DataObjectCollection {
	function __construct() {
		parent::__construct('CompanySource');
	}
}



class CompanyStatus extends DataObject {
	function __construct() {
		parent::__construct('company_statuses');
	}
}
class CompanyStatusCollection extends DataObjectCollection {
	function __construct() {
		parent::__construct('CompanyStatus');
	}
}



class CompanyType extends DataObject {
	function __construct() {
		parent::__construct('company_types');
	}

}
class CompanyTypeCollection extends DataObjectCollection {
	function __construct() {
		parent::__construct('CompanyType');
	}
}




?>