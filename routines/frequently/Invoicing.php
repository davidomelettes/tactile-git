<?php
require_once 'Service/Xero.php';

class Invoicing extends EGSCLIApplication {
    
    const PREMIUM_PER_USER = 6.00;
    const ENTERPRISE_PER_USER = 12.00;
    
	// This is a lookup for names xero can't handle
	private $INVOICE_NAMES = array(
		'gactechnologies' => 'GAC Technologies'
	);
	
	public function getVATForCountry($code, $time = 'now')
	{
	    $when = strtotime($time);
	    switch ($code) {
	        case 'LU': // Luxembourg
	            return 0.17;
	        case 'MT': // Malta
	            return 0.18;
	        case 'CY': // Cyprus
	        case 'DE': // Germany
	            return 0.19;
	        case 'AT': // Austria
	        case 'BG': // Bulgaria
	        case 'EE': // Estonia
	        case 'FR': // France
	        case 'SK': // Slovakia
	            return 0.20;
	        case 'BE': // Belgium
	        case 'CZ': // Czech Republic
	        case 'LV': // Latvia
	        case 'LT': // Lithuania
	        case 'NL': // Netherlands
	        case 'ES': // Spain
	            return 0.21;
	        case 'IT': // Italy
	        case 'SI': // Slovenia
	            return 0.22;
	        case 'GR': // Greece
	        case 'IE': // Ireland
	        case 'PL': // Poland
	        case 'PT': // Portugal
	            return 0.23;
	        case 'FI': // Finland
	        case 'RO': // Romania
	            return 0.24;
	        case 'HR': // Croatia
	        case 'DK': // Denmark
	        case 'SE': // Sweden
	            return 0.25;
	        case 'HU': // Hungary
	            return 0.27;
	        case 'GB':
	        case 'UK':
	            if ($when > strtotime('2015-01-01 00:00:00')) {
	                // Deregistered for VAT
	                return 0;
	            } else {
	                return 0.20;
	            }
	        default:
	            return 0;
	    }
	}
	
	/*
	private $VAT_COUNTRIES = array(
		'AT','BE','BG','CY','CZ','DK','EE','FI','FR',
		'DE','EL','HU','IE','IT','LV','LT','LU','MT',
		'NL','PL','PT','RO','SK','SI','ES','SE'
	);
	*/
	
	private $_countries = array();
	private $xero;
	private $_start_date = '2009-03-18';
	//private $_VAT = 0.175;
	private $_plans = array (
		'Micro',
		'SME',
		'Business',
		'Premier'
	);
	
	private $_plan_amounts = array (
		6 => 'Micro',
		15 => 'SME',
		35 => 'Business',
		60 => 'Premier'
	);
	
	public function go() {
		// This is the time for 4th Jan 2011: 1294099200
		/*
		if(time() > 1294099200) {
			$this->_VAT = 0.2;
		}
		*/
		$db = DB::Instance();
		require_once 'Zend/Log.php';
		$this->xero = new Service_Xero(XERO_INVOICING_PROVIDER_KEY, XERO_INVOICING_CUSTOMER_KEY, PRODUCTION);
		$country_model = new Country();
		$this->_countries = $country_model->getAll();
		//$db->StartTrans();
		
		require_once 'Tactile/Api.php';
		if(PRODUCTION) {
			$client = new Tactile_Api(TACTILE_API_DOMAIN, TACTILE_API_KEY);
		} else {
			$client = new Tactile_Api(TACTILE_API_DOMAIN, TACTILE_API_KEY, null, TACTILE_API_TEST_DOMAIN);
		}
							
		// First up we get new accounts not in Xero - pre 2009/03/18 are Senokian
		$query = "SELECT site_address, company, CASE WHEN country_code IS NULL THEN 'US' ELSE country_code END AS country_code, email, firstname, surname, vat_number
			FROM tactile_accounts
			WHERE site_address NOT IN (
				SELECT accountnumber
				FROM organisations
				WHERE usercompanyid=74607 AND
					xero_id IS NOT NULL
			) AND 
			id IN (
				SELECT account_id
				FROM payment_records
				WHERE type IN ('FULL', 'RELEASE', 'REPEAT') AND
				created>'" . $this->_start_date . "'
			)";

		$contacts = $db->getArray($query);

		// Invoicing is 2 stage, we'll add new contacts, then start again and do actual invoices
		foreach ($contacts as $contact)	{
	
			$xero_contact = $this->do_xero_contact($contact);
	
			$query = 'SELECT id FROM organisations WHERE usercompanyid=74607 AND accountnumber='.$db->qstr($contact['site_address']);
			$tactile_org_id = $db->getOne($query);
			// Add the ContactID to our DB
			if(empty($xero_contact->ContactID) || empty($tactile_org_id)) {
				// Contact doesn't exist so add one to our Tactile CRM via the API
				if($tactile_org_id === false) {
					try {
						require_once 'Tactile/Api/Organisation.php';
							
						$org = new Tactile_Api_Organisation();
						$org->accountnumber = $contact['site_address'];
						$org->name = $contact['company'];
						//$org->vatnumber=$contact['vat_number'];
						$org->country_code = $contact['country_code'];

						$new_org = $client->saveOrganisation($org);

						if($new_org->status == "success") {
							$tactile_org_id = $new_org->id;
						}

						if(!empty($tactile_org_id)) {
							// Nothing has gone wrong adding the org so we'll add the person too
							require_once 'Tactile/Api/Person.php';

							$person = new Tactile_Api_Person();
							// Add a new person
							$person = new Tactile_Api_Person();
							$person->organisation_id = $tactile_org_id;
								
							$person->firstname = $contact['firstname'];
							$person->surname = $contact['surname'];
							$person->email = $contact['email'];

							$new_person = $client->savePerson($person);
						}

					} catch (Exception $e) {
					  require_once 'Zend/Log.php';
					  $logger = new Zend_Log(new Log_Writer_Mail(NOTIFICATIONS_TO, 'Error updating our Tactile CRM via the API'));
					  $logger->crit($e->getMessage());
					  $logger->crit($e->getTraceAsString());
					}
				}				
			}
			
			// Now we have a Xero ID for the org so we'll update our records. This will stop us adding in the future
			$query = 'UPDATE organisations SET xero_id='.$db->qstr($xero_contact->ContactID).' WHERE usercompanyid=74607 AND accountnumber='.$db->qstr($contact['site_address']).' AND id='.$db->qstr($tactile_org_id);
			
			$db->StartTrans();
			$db->execute($query);
			$db->CompleteTrans();
		}
		
		// Get all the uninvoiced transactions (we lower case the description due to descrepencies in the invoicing code)
		$query = "SELECT r.id, r.account_id, r.auth_code, p.name AS plan, p.cost_per_month, a.site_address, a.company, CASE WHEN a.country_code IS NULL THEN 'US' ELSE a.country_code END AS country_code, a.email, a.firstname, a.surname, a.vat_number, r.amount, lower(r.description) AS description, r.trans_id, date_trunc('day', r.created) AS created, a.notes, a.telephone
		FROM account_plans p, tactile_accounts a, payment_records r WHERE r.invoiced = false AND p.id=a.current_plan_id AND a.id=r.account_id AND r.type IN ('FULL', 'RELEASE', 'REPEAT') AND r.created>'" . $this->_start_date . "'";
		
		$query .= " AND r.account_id<> 4687 ORDER BY created ASC";

		$payments = $db->getArray($query);

		foreach($payments AS $payment) {
			// Update the contact in Xero and get the ID
			$contact = array();
			$contact['site_address'] = $payment['site_address'];
			
			// This is for wierd names Xero can't handle
			if(isset($this->INVOICE_NAMES[$contact['site_address']])) {
				$contact['company'] = $this->INVOICE_NAMES[$contact['site_address']];
			} else {
				$contact['company'] = $payment['company'];
			}
			
			$contact['country_code'] = $payment['country_code'];
			$contact['email'] = $payment['email'];
			$contact['firstname'] = $payment['firstname'];
			$contact['surname'] = $payment['surname'];
			$contact['vat_number'] = $payment['vat_number'];

			$xero_contact = $this->do_xero_contact($contact);

			$xero_invoice = new Service_Xero_Entity_Invoice($this->xero);
			$xero_invoice->set('InvoiceType', Service_Xero_Entity_Invoice::TYPE_RECEIVABLE);
			$xero_invoice->setContact($xero_contact);
			
			$vat_total = 0;
			
			$xero_lineitem = new Service_Xero_Entity_Invoice_LineItems_LineItem($this->xero);
			// Check if we need to charge VAT
			$tax = false;
			$taxRate = $this->getVATForCountry($payment['country_code'], $payment['created']);
			$tax_type = Service_Xero_Entity_Invoice_LineItems_LineItem::TAX_ZERORATEDOUTPUT;
			if ($taxRate > 0) {
			    $tax = 'true';
			    $tax_type = Service_Xero_Entity_Invoice_LineItems_LineItem::TAX_OUTPUT;
			}
			/*
			if($payment['country_code'] == 'GB') {
				// In the UK so all have VAT
				$tax = 'true';
				$tax_type = Service_Xero_Entity_Invoice_LineItems_LineItem::TAX_OUTPUT;
			} else if(in_array($payment['country_code'], $this->VAT_COUNTRIES) && empty($payment['vat_number'])) {
				$tax = 'true';
				$tax_type = Service_Xero_Entity_Invoice_LineItems_LineItem::TAX_OUTPUT;
			} else {
				$tax = 'false';
				$tax_type = Service_Xero_Entity_Invoice_LineItems_LineItem::TAX_ZERORATEDOUTPUT;
				$VAT = '0.00';
			}
			*/
			
			$xero_lineitem // You can also use fluid interfaces
				->set('AccountCode', 200) // Shrug. Our own ref for the line item?
				->set('Tracking', ''); // Must be present, but can be blank
				
		// Now we need to match up transaction descriptions
			if(
			strpos($payment['description'], '(30 days)') !== false ||
			strpos($payment['description'], '(31 days)') !== false
			) {
				// This must mean we're invoicing a full 30 day payment
				if($payment['plan'] == 'Premium' && (strpos($payment['description'], '@') !== false)) {
					// This is a premium Plan (30 Day/Full), i.e. a per user payment
					$unit_amount = round(self::PREMIUM_PER_USER / (1 + $taxRate), 2);
					$tax_amount = round(self::PREMIUM_PER_USER - $unit_amount, 2);
					/*
					if($tax == 'true') {
						// This is to take into account the VAT change
						if($this->_VAT == 0.15) {
							$unit_amount = 5.217;
							$tax_amount = 0.783;
						} else 	if($this->_VAT == 0.2) {
							$unit_amount = 5.000;
							$tax_amount = 1.000;
						} else {
							$unit_amount = 5.106;
							$tax_amount = 0.894;
						}
					} else {
						$unit_amount = 6;
						$tax_amount = 0;
					}
					*/
					$xero_lineitem
						->set('Description', 'Tactile CRM Premium Subscription (per user)')
						->set('Quantity', intval($payment['amount']/self::PREMIUM_PER_USER))
						->set('UnitAmount', round($unit_amount, 2))
						->set('LineAmount', round($unit_amount * intval($payment['amount']/self::PREMIUM_PER_USER), 2))
						->set('TaxType', $tax_type) // There are several different types
						->set('TaxAmount', round($tax_amount * intval($payment['amount']/self::PREMIUM_PER_USER), 2));
					
					$total_vat = round($tax_amount * intval($payment['amount']/6), 2);
				} 
				else if($payment['plan'] == 'Enterprise') {
					// This is an enterprise Plan (30 Day/Full), i.e. a per user payment
				    $unit_amount = round(self::ENTERPRISE_PER_USER / (1 + $taxRate), 2);
				    $tax_amount = round(self::ENTERPRISE_PER_USER - $unit_amount, 2);
					/*
					if($tax == 'true') {
						if($this->_VAT == 0.15) {
							$unit_amount = 10.435;
							$tax_amount = 1.565;
						} else 	if($this->_VAT == 0.2) {
							$unit_amount = 10.000;
							$tax_amount = 2.000;
						} else {
							$unit_amount = 10.213;
							$tax_amount = 1.787;
						}
					} else {
						$unit_amount = 12;
						$tax_amount = 0;
					}
					*/
					$xero_lineitem
						->set('Description', 'Tactile CRM Enterprise Subscription (per user)')
						->set('Quantity', intval($payment['amount']/self::ENTERPRISE_PER_USER))
						->set('UnitAmount', round($unit_amount, 2))
						->set('LineAmount', round($unit_amount * intval($payment['amount']/self::ENTERPRISE_PER_USER), 2))
						->set('TaxType', $tax_type) // There are several different types
						->set('TaxAmount', round($tax_amount * intval($payment['amount']/self::ENTERPRISE_PER_USER), 2));
					
					$total_vat = round($tax_amount * intval($payment['amount']/self::ENTERPRISE_PER_USER), 2);
				} else {
					// This is an old style plan (30 Day/Full) payment
					if($tax == 'true') {
						$unit_amount = round($payment['amount'] / (1 + $taxRate), 2);
						$tax_amount = round($payment['amount'] - $unit_amount, 2);
					} else {
						$unit_amount = $payment['amount'];
						$tax_amount = 0;
					}
					$xero_lineitem
						->set('Description', 'Tactile CRM '.$this->_plan_amounts[intval($payment['amount'])].' Subscription')
						->set('Quantity', 1)
						->set('UnitAmount', $unit_amount)
						->set('LineAmount', $unit_amount)
						->set('TaxType', $tax_type) // There are several different types
						->set('TaxAmount', $tax_amount);

					$total_vat = $tax_amount;
				}
			} else {
				if($payment['plan'] == 'Premium') {
					if(strpos($payment['description'], '@') !== false) {
						// This is a pro-rata payment
						$details = explode('@', $payment['description']);
						$users = trim(str_replace('users', '', $details[0]));
						preg_match('/\(.+\)/', $details[1], $days);
						$days = str_replace('(', '', str_replace(')', '', $days[0]));
						
						if($tax == 'true') {
							$unit_amount = round(($payment['amount'] / (1 + $taxRate)) / $users, 2);
							$tax_amount = round(($payment['amount'] / $users) - $unit_amount, 2);
						} else {
							$unit_amount = round($payment['amount']/$users, 3);
							$tax_amount = 0;
						}
						$xero_lineitem
							->set('Description', 'Tactile CRM Upgrade (per user) - Pro Rata '.$days)
							->set('Quantity', $users)
							->set('UnitAmount', $unit_amount)
							->set('LineAmount', $unit_amount * $users)
							->set('TaxType', $tax_type) // There are several different types
							->set('TaxAmount', $tax_amount * $users);

						$total_vat = $tax_amount * $users;
						
					} else if(strpos($payment['description'], 'changing plan from solo to premium with') !== false) {
						// An upgrade from solo to premium
						$users = trim(str_replace('users', '', str_replace('changing plan from solo to premium with', '', $payment['description'])));
						
						$unit_amount = round(self::PREMIUM_PER_USER / (1 + $taxRate), 2);
						$tax_amount = round(self::PREMIUM_PER_USER - $unit_amount, 2);
						/*
						if($tax == 'true') {
							if($this->_VAT == 0.15) {
								$unit_amount = 5.217;
								$tax_amount = 0.783;
							} else 	if($this->_VAT == 0.2) {
								$unit_amount = 5.000;
								$tax_amount = 1.000;
							} else {
								$unit_amount = 5.106;
								$tax_amount = 0.894;
							}
						} else {
							$unit_amount = 6;
							$tax_amount = 0;
						}
						*/
						$xero_lineitem
							->set('Description', 'Tactile CRM Upgrade (per user)')
							->set('Quantity', $users)
							->set('UnitAmount', round($unit_amount, 2))
							->set('LineAmount', round($unit_amount * $users, 2))
							->set('TaxType', $tax_type) // There are several different types
							->set('TaxAmount', round($tax_amount * $users, 2));

						$total_vat = round($tax_amount * $users, 2);
					} else {
						// They are on a premium plan and have been moved from an old style plan, or manual payment
						if($tax == 'true') {
							$unit_amount = round($payment['amount'] / (1 + $taxRate), 2);
							$tax_amount = round($payment['amount'] - $unit_amount, 2);
						} else {
							$unit_amount = $payment['amount'];
							$tax_amount = 0;
						}
						$xero_lineitem
							->set('Description', 'Tactile CRM Subscription (per user)')
							->set('Quantity', 1)
							->set('UnitAmount', $unit_amount)
							->set('LineAmount', $unit_amount)
							->set('TaxType', $tax_type) // There are several different types
							->set('TaxAmount', $tax_amount);

						$total_vat = $tax_amount;
					}
				} else if($payment['plan'] == 'Enterprise') {
					if(strpos($payment['description'], '@') !== false) {
						// This is a pro-rata payment
						$details = explode('@', $payment['description']);
						$users = trim(str_replace('users', '', $details[0]));
						preg_match('/\(.+\)/', $details[1], $days);
						$days = str_replace('(', '', str_replace(')', '', $days[0]));
						
						if($tax == 'true') {
							$unit_amount = round(($payment['amount'] / (1 + $taxRate)) / $users, 2);
							$tax_amount = round(($payment['amount'] / $users) - $unit_amount, 2);
						} else {
							$unit_amount = round($payment['amount']/$users, 3);
							$tax_amount = 0;
						}
						$xero_lineitem
							->set('Description', 'Tactile CRM Upgrade (per user) - Pro Rata '.$days)
							->set('Quantity', $users)
							->set('UnitAmount', $unit_amount)
							->set('LineAmount', $unit_amount * $users)
							->set('TaxType', $tax_type) // There are several different types
							->set('TaxAmount', $tax_amount * $users);

						$total_vat = $tax_amount * $users;
						
					}
				} else if (strpos($payment['description'], 'changing plan from') !== false){
					// This is an old style plan change
					if($tax == 'true') {
						$unit_amount = round($payment['amount'] / (1 + $taxRate), 2);
						$tax_amount = round($payment['amount'] - $unit_amount, 2);
					} else {
						$unit_amount = $payment['amount'];
						$tax_amount = 0;
					}
					
					$xero_lineitem
						->set('Description', 'Tactile CRM Upgrade ' . ucwords(str_replace('changing plan', '', $payment['description'])))
						->set('Quantity', 1)
						->set('UnitAmount', $unit_amount)
						->set('LineAmount', $unit_amount)
						->set('TaxType', $tax_type) // There are several different types
						->set('TaxAmount', $tax_amount);

					$total_vat = $tax_amount;
				} else if(in_array($this->_plan_amounts[intval($payment['amount'])], $this->_plans)) {
					// We have no description for the amount so try to match to a plan
					$plan = $this->_plan_amounts[intval($payment['amount'])];
					
					if($tax == 'true') {
						$unit_amount = round($payment['amount'] / (1 + $taxRate), 2);
						$tax_amount = round($payment['amount'] - $unit_amount, 2);
					} else {
						$unit_amount = $payment['amount'];
						$tax_amount = 0;
					}
					
					$xero_lineitem
						->set('Description', 'Tactile CRM ' . $plan . ' Subscription')
						->set('Quantity', 1)
						->set('UnitAmount', $unit_amount)
						->set('LineAmount', $unit_amount)
						->set('TaxType', $tax_type) // There are several different types
						->set('TaxAmount', $tax_amount);
						
						$total_vat = $tax_amount;						
				} else {
					// WTF
				}
			}
		
			$xero_invoice->addLineItem($xero_lineitem);
			
			$invoice_number = $payment['id'].'/'.$payment['account_id'].'/'.$payment['auth_code'];
			
			$xero_invoice
				->set('InvoiceNumber', $invoice_number) 
				->set('Reference', $payment['trans_id']) // Human-readable ref
				->set('InvoiceDate', date('c', strtotime($payment['created'])))
				->set('DueDate', date('c', strtotime($payment['created'])))
				->set('TaxInclusive', $tax)
				->set('IncludesTax', $tax)
				->set('SubTotal', $payment['amount'] - $total_vat)
				->set('TotalTax', $total_vat)
				->set('Total', $payment['amount']);

			try {
				//$xero_invoice->put();
			} catch (Service_Xero_Exception $e) {
				$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Error saving invoice to Xero'));
				$logger->crit($e->getMessage());
				$logger->crit($e->getTraceAsString());
				$logger->crit('Transaction ID: '. $payment['trans_id']);
				$logger->crit('Invoice ID: '. $invoice_number);

				continue;
			}
			
		// Now we have a Xero ID for the org so we'll update our records. This will stop us adding in the future
			$query = 'UPDATE payment_records SET xero_invoice_id='.$db->qstr($invoice_number).', invoiced=true WHERE id='.$db->qstr($payment['id']);
			
			$db->StartTrans();
			$db->execute($query);
			$db->CompleteTrans();
			
			$mail = new Omelette_Mail('invoice');

			$mail->getMail()->setSubject("Your Receipt (".$invoice_number.")");
			$mail->getMail()->setFrom(TACTILE_EMAIL_FROM, TACTILE_EMAIL_NAME);

			$mail->getView()->set('firstname', trim($payment['firstname']));
			$mail->getView()->set('surname', trim($payment['surname']));
			$mail->getView()->set('company', trim($payment['company']));
			$mail->getView()->set('site_address', $payment['site_address']);
			$mail->getView()->set('auth_code', $payment['auth_code']);
			$mail->getView()->set('payment_date', date('j F Y', strtotime($payment['created'])));
			$mail->getView()->set('invoice_number', $invoice_number);
			$mail->getView()->set('description', $xero_invoice->LineItems->LineItem[0]->Description);
			$mail->getView()->set('quantity', $xero_invoice->LineItems->LineItem[0]->Quantity);
			$mail->getView()->set('line_amount', '&pound;'.number_format($xero_invoice->LineItems->LineItem[0]->UnitAmount, 2));
			$mail->getView()->set('line_total', '&pound;'.number_format($xero_invoice->LineItems->LineItem[0]->LineAmount, 2));
			$mail->getView()->set('sub_total', '&pound;'.number_format($payment['amount'] - $total_vat, 2));
			$mail->getView()->set('vat', '&pound;'.number_format($total_vat, 2));
			$mail->getView()->set('vat_rate', $this->getVATForCountry($payment['country_code'])*100);
			$mail->getView()->set('total', '&pound;'.number_format($payment['amount'], 2));
			$mail->getView()->set('total', '&pound;'.number_format($payment['amount'], 2));
			$mail->getView()->set('country', $this->_countries[$payment['country_code']]);
			
			$notes = $this->expandNotes($payment['notes']);

			// We also want to remove them from the trial reminder emails
			try {
				$soapClient = @new SoapClient("http://api.createsend.com/api/api.asmx?wsdl");

				$response = $soapClient->Unsubscribe(
					array(
						'ApiKey' => OMELETTES_CM_API_KEY,
						'ListID' => '73634f379b469c71e8e31bf78104c777',
						'Email' => $payment['email']
					)
				);
			} catch (Exception $e) {
				require_once 'Zend/Log.php';
				$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Campaign monitor removing from trial reminder as purchasing'));
				$logger->crit($e->getMessage());
				$logger->crit($e->getTraceAsString());
			}


			// We also want to remove them from the free trial reminder emails
			try {
				$soapClient = @new SoapClient("http://api.createsend.com/api/api.asmx?wsdl");

				$response = $soapClient->Unsubscribe(
					array(
						'ApiKey' => OMELETTES_CM_API_KEY,
						'ListID' => '5230d0f588eda4a723fc81418f421919',
						'Email' => $payment['email']
					)
				);
			} catch (Exception $e) {
				require_once 'Zend/Log.php';
				$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Campaign monitor removing from free trial reminder as purchasing'));
				$logger->crit($e->getMessage());
				$logger->crit($e->getTraceAsString());
			}

			try {
				// Only email if payment was in last 3 days
				if(strtotime($payment['created']) >= (time()-259200)) {

					$query = 'SELECT id FROM organisations WHERE usercompanyid=74607 AND accountnumber='.$db->qstr($payment['site_address']);
					$tactile_org_id = $db->getOne($query);
					require_once 'Tactile/Api/Organisation.php';
				
					// Get all the opps added in the last 30 days for this org.
					$opps = $client->getOpportunities(array('organisation_id' => $tactile_org_id, 'created_after' => '-31 days'));

					// We have to make the assumption there is only one opp max
					$o = false;
					$total = 0;	
				
					require_once 'Tactile/Api/Opportunity.php';

					if($opps->status == "success") {
					
						foreach($opps->opportunities AS $opp) {
							if(
								$opp->assigned_to == 'website//team' &&
								$opp->status == 'In Discussion' &&
								$opp->type == 'Tactile CRM' && 
								$opp->source == 'Website') {

								$o = true;
								$total++;
				
								$update_opp = new Tactile_Api_Opportunity();	

								foreach($opp as $key => $value) {
                                                			if(!empty($value)) {
                                                   			     $update_opp->$key = $value;
                                              				  }
                                        			}
								
								$update_opp->name = $xero_invoice->LineItems->LineItem[0]->Description;

								if($xero_invoice->LineItems->LineItem[0]->Quantity > 1) $update_opp->name = str_replace('per user', $xero_invoice->LineItems->LineItem[0]->Quantity.' users', $update_opp->name);
								else $update_opp->name = str_replace('per user', $xero_invoice->LineItems->LineItem[0]->Quantity.' user', $update_opp->name);

								$update_opp->probability = 100;
								$update_opp->enddate = date('Y-m-d');
								$update_opp->archived = true;
								$update_opp->cost = number_format($payment['amount'], 2);
								$update_opp->status_id = 4487; 
								$update_opp->source_id = 2688; 

								$update_opp->description = 'Updated automatically by the invoicing script' . $update_opp->description;
	
								$opp = $client->saveOpportunity($update_opp);

								if($opp->status == 'success') {
									$opp_id = $opp->id;
								}
							}
						}

						// Was there an error with more than one opp?
						if($total > 1) {
							mail(DEBUG_EMAIL_ADDRESS, 'Error Updating Opportunities', 'Error updating opportunities against http://team.tactilecrm.com/organisations/view/'.$tactile_org_id.' there was more than one');
						}
					}

					// Need to add an opportunity as we couldn't update one
					if(!$o && false) {
						$new_opp = new Tactile_Api_Opportunity();	

						$new_opp->name = $xero_invoice->LineItems->LineItem[0]->Description;

						if($xero_invoice->LineItems->LineItem[0]->Quantity > 1) $new_opp->name = str_replace('per user', $xero_invoice->LineItems->LineItem[0]->Quantity.' users', $new_opp->name);
						else $new_opp->name = str_replace('per user', $xero_invoice->LineItems->LineItem[0]->Quantity.' user', $new_opp->name);

						$new_opp->probability = 100;
						$new_opp->enddate = date('Y-m-d');
						$new_opp->archived = true;
						$new_opp->cost = number_format($payment['amount'], 2);
						$new_opp->status_id = 4487; 
						$new_opp->source_id = 2688; 
						$new_opp->type_id = 2413; 
						$new_opp->assigned_to = 'website//team';
						$new_opp->organisation_id = $tactile_org_id;

						$opp = $client->saveOpportunity($new_opp);

						if($opp->status == 'success') {
							$opp_id = $opp->id;
							$o = true;
							$total = 1;
						}
					}

					if (defined('PRODUCTION') && PRODUCTION == true) {
						// Send to the opp dropbox if possible
						if($o && $total == 1 && isset($opp_id)) {
							$mail->addBcc(str_replace('dropbox', 'opp+'.$opp_id, TACTILE_DROPBOX_WEBSITE));
						} else {
							$mail->addBcc(TACTILE_DROPBOX_WEBSITE);
						}
						$mail->getMail()->addTo($payment['email']);
						$mail->addBcc('accounts@omelett.es');
					} else {
						$mail->getMail()->addTo(DEBUG_EMAIL_ADDRESS);
					}

					$mail->send();	

					$query = 'SELECT a.account_expires, a.per_user_limit, p.name, p.cost_per_month, p.per_user
						FROM tactile_accounts a, account_plans p
						WHERE
							p.id=a.current_plan_id AND 
							a.id='.$db->qstr($payment['account_id']);

					$plan_renewal = $db->getRow($query);

					$new_opp = new Tactile_Api_Opportunity();
					$new_opp->probability = 85;
					$new_opp->enddate = date('Y-m-d', strtotime($plan_renewal['account_expires']));

					if($plan_renewal['per_user'] == 't') {
						$new_opp->cost = $plan_renewal['per_user_limit'] * $plan_renewal['cost_per_month'];
						if( $plan_renewal['per_user_limit'] > 1) {
							$new_opp->name = 'Tactile CRM '.$plan_renewal['name'].' Subscription ('.$plan_renewal['per_user_limit'].' users)';
						} else {
							$new_opp->name = 'Tactile CRM '.$plan_renewal['name'].' Subscription ('.$plan_renewal['per_user_limit'].' user)';
						}
					} else {
						$new_opp->cost = $plan_renewal['cost_per_month'];
						$new_opp->name = 'Tactile CRM '.$plan_renewal['name'].' Subscription';
					}

					$new_opp->status_id = 4486;
					$new_opp->source_id = 2688;
					$new_opp->type_id = 2413;
					$new_opp->assigned_to = 'website//team';
					$new_opp->organisation_id = $tactile_org_id;
				
					$opp = $client->saveOpportunity($new_opp);

					if($opp->status != 'success') {
						mail(DEBUG_EMAIL_ADDRESS, 'Error adding opportunity for next month\'s renewal', 'Trying to add an opportunity for the renewal of payment '.$payment['id']);
					}
					
				}
			} catch (Zend_Mail_Transport_Exception $e) {
				require_once 'Zend/Log.php';
				$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Invoice Email Problem'));
				$logger->crit($e->getMessage());
				$logger->crit($e->getTraceAsString());
			}
		}
		
	}

	// Returns a Xero ContactID
	private function do_xero_contact($contact) {
		// Create a contact (we put the account owner's name in as well as the company as Xero don't have people)
		$xero_contact = new Service_Xero_Entity_Contact($this->xero);
		
		//$xero_contact->set('Name', $contact['company'] . ' (' . $contact['firstname'] . ' ' . $contact['surname'] . ')');
		$xero_contact->set('Name', $contact['company']);
		$xero_contact->set('EmailAddress', $contact['email']);
		
		$xero_address = new Service_Xero_Entity_Contact_Addresses_Address($this->xero);
		$xero_address->set('AddressType', Service_Xero_Entity_Contact_Addresses_Address::TYPE_STREET);
		$xero_address->set('Country', $this->_countries[$contact['country_code']]);
		
		$xero_contact->addAddress($xero_address);
		
		try {
			//$xero_contact->put();
		} catch (Service_Xero_Exception $e) {
			$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Error saving contact to Xero'));
			$logger->crit('Account\'s Site Address: '.$contact['site_address']);
		} catch (Exception $e) {
			$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Error with Invoicing'));
			$logger->crit($e->getMessage());
			$logger->crit($e->getTraceAsString());
		}
		return $xero_contact;
	}

	function expandNotes($notes) {
		$note = explode('::', $notes);

		$parsed_notes = array();

		while($data = array_pop($note)) {
			$tmp_note = explode('//', $data);
			$parsed_notes[$tmp_note[0]] = $tmp_note[1];
		}

		return $parsed_notes;
	}

}
