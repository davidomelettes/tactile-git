<?php

class GenerateInvoiceCsv extends EGSCLIApplication
{
	const PREMIUM_PER_USER = 6.00;
	const ENTERPRISE_PER_USER = 12.00;
	const TAX_TYPE_ZERO = 'ZERORATEDOUTPUT';
	const TAX_TYPE_VAT = 'OUTPUT2';
	const VAT = 20.00;
	
	#const STARTING_DATE = '2008-05-08 00:00:00';
	const STARTING_DATE = '2012-01-01 00:00:00';
	
	protected $_vatCountries = array(
		'AT','BE','BG','CY','CZ','DK','EE','FI','FR',
		'DE','EL','HU','IE','IT','LV','LT','LU','MT',
		'NL','PL','PT','RO','SK','SI','ES','SE'
	);
	
	public function go()
	{
		$this->logger->addWriter(new Zend_Log_Writer_Stream('php://output'));
		
		$db = DB::Instance();
		$csv = fopen('tactile_invoices.csv', 'w');
		$headersWritten = false;
		
		$sql = "SELECT
				r.id||'/'||r.account_id||'/'||r.auth_code as \"InvoiceNumber\",
				a.company as \"ContactName\",
				a.email as \"EmailAddress\",
				a.vat_number as vat_number,
				c.code as country_code,
				c.name as \"POCountry\",
				r.trans_id as \"Reference\",
				r.created as \"InvoiceDate\",
				r.created as \"DueDate\",
				r.amount as \"Total\",
				p.name as plan_name,
				r.description as \"Description\",
				'1' as \"Quantity\",
				'0' as \"UnitAmount\",
				'200' as \"AccountCode\",
				'".self::TAX_TYPE_ZERO."' as \"TaxType\",
				'0' as \"TaxAmount\"
			FROM
				account_plans p,
				tactile_accounts a,
				payment_records r,
				countries c
			WHERE
				p.id = a.current_plan_id
				AND a.id = r.account_id
				AND c.code = a.country_code
				AND r.type IN ('FULL', 'RELEASE', 'REPEAT')
				AND (r.created > '".self::STARTING_DATE."')
			ORDER BY r.created ASC
		";
		
		$this->logger->debug('Fetching Invoices...');
		$payments = $db->getArray($sql);
		if (!is_array($payments)) {
			throw new Exception('No results returned: ' . $db->ErrorMsg());
		}
		$this->logger->debug(count($payments) . " invoices fetched");
		foreach ($payments as $payment) {
			$this->logger->debug('Processing invoice #' . $payment['InvoiceNumber']);
			
			// Do we tax them?
			$taxed = false;
			if (
				$payment['county_code'] === 'GB' ||
				(in_array($payment['county_code'], $this->_vatCountries))
			) {
				// In the UK, or are in a taxable country and have given us a VAT number
				$taxed = true;
				$payment['TaxType'] = self::TAX_TYPE_VAT;
			}
			
			// Sort descriptions and amounts out
			if (preg_match('/\(3[01] days\)/', $payment['Description'])) {
				// We are charging for a full month's subscription
				$payment['Description'] = 'Tactile CRM ' . $payment['plan_name'] . ' Subscription (per user)';
				
				if ($payment['plan_name'] === 'Premium') {
					// Divide total by per-user cost for qty
					$payment['Quantity'] = intval($payment['Total'] / self::PREMIUM_PER_USER);
					
					// Adjust amounts according to taxation
					$payment['UnitAmount'] = $taxed ? (round(self::PREMIUM_PER_USER * (1-self::VAT), 2)) : self::PREMIUM_PER_USER;
					$payment['TaxAmount'] = $taxed ? (round($payment['Total'] * self::VAT, 2)) : '0.00';
					
				} else {
					// Divide total by per-user cost for qty
					$payment['Quantity'] = intval($payment['Total'] / self::ENTERPRISE_PER_USER);
					
					// Adjust amounts according to taxation
					$payment['UnitAmount'] = $taxed ? (round(self::ENTERPRISE_PER_USER * (1-self::VAT), 2)) : self::ENTERPRISE_PER_USER;
					$payment['TaxAmount'] = $taxed ? (round($payment['Total'] * self::VAT, 2)) : '0.00';
				}
				
			} elseif (preg_match('/@/', $payment['Description'])) {
				// Pro-rata charge for purchasing users
				$details = explode('@', $payment['Description']);
				$users = trim(str_replace('users', '', $details[0]));
				preg_match('/\(.+\)/', $details[1], $days);
				$days = str_replace('(', '', str_replace(')', '', $days[0]));
				
				$payment['Description'] = 'Tactile CRM Upgrade (per user) - Pro Rata ' . $days;
				$payment['Quantity'] = $users;
				
				$payment['UnitAmount'] = $taxed ? (round($payment['Total'] * (1-self::VAT) / $users, 2)) : (round($payment['Total'] / $users, 2));
				$payment['TaxAmount'] = $taxed ? (round($payment['Total'] * self::VAT, 2)) : '0.00';
				
			} elseif (preg_match('/Changing plan/', $payment['Description'])) {
				// Plan upgrade
				if ($payment['plan_name'] === 'Premium') {
					// Upgrading from Solo to Premium
					$users = trim(str_replace('users', '', str_replace('changing plan from solo to premium with', '', $payment['Description'])));
					
					$payment['Description'] = 'Tactile CRM Upgrade (per user)';
					$payment['Quantity'] = $users;
					
					$payment['UnitAmount'] = $taxed ? (round(self::PREMIUM_PER_USER * (1-self::VAT), 2)) : self::PREMIUM_PER_USER;
					$payment['TaxAmount'] = $taxed ? (round($payment['Total'] * self::VAT, 2)) : '0.00';
					
				} else {
					// Upgrading from Solo/Premium to Enterprise
					$payment['Description'] = 'Tactile CRM Enterprise Upgrade';
					$payment['Quantity'] = 1;
					
					$payment['UnitAmount'] = $taxed ? (round($payment['Total'] * (1-self::VAT), 2)) : $payment['Total'];
					$payment['TaxAmount'] = $taxed ? (round($payment['Total'] * self::VAT, 2)) : '0.00';
				}
				
			} elseif (empty($payment['Description'])) {
				// Empty description
				$payment['Description'] = 'TACTILE CRM SUBSCRIPTION UPGRADE';
				$payment['Quantity'] = 1;
					
				$payment['UnitAmount'] = $taxed ? (round($payment['Total'] * (1-self::VAT), 2)) : $payment['Total'];
				$payment['TaxAmount'] = $taxed ? (round($payment['Total'] * self::VAT, 2)) : '0.00';
				
			} elseif (preg_match('/Business/', $payment['Description'])) {
				// Old Business account
				$payment['Description'] = 'TACTILE CRM BUSINESS SUBSCRIPTION';
				$payment['Quantity'] = 1;
					
				$payment['UnitAmount'] = $taxed ? (round($payment['Total'] * (1-self::VAT), 2)) : $payment['Total'];
				$payment['TaxAmount'] = $taxed ? (round($payment['Total'] * self::VAT, 2)) : '0.00';
				
			} elseif (preg_match('/Premier/', $payment['Description'])) {
				// Old Premier account
				$payment['Description'] = 'TACTILE CRM PERMIER SUBSCRIPTION';
				$payment['Quantity'] = 1;
					
				$payment['UnitAmount'] = $taxed ? (round($payment['Total'] * (1-self::VAT), 2)) : $payment['Total'];
				$payment['TaxAmount'] = $taxed ? (round($payment['Total'] * self::VAT, 2)) : '0.00';
				
			} elseif (preg_match('/SME/', $payment['Description'])) {
				// Old SME account
				$payment['Description'] = 'TACTILE CRM SME SUBSCRIPTION';
				$payment['Quantity'] = 1;
					
				$payment['UnitAmount'] = $taxed ? (round($payment['Total'] * (1-self::VAT), 2)) : $payment['Total'];
				$payment['TaxAmount'] = $taxed ? (round($payment['Total'] * self::VAT, 2)) : '0.00';
				
			} elseif (preg_match('/Micro/', $payment['Description'])) {
				// Old Micro account
				$payment['Description'] = 'TACTILE CRM MICRO SUBSCRIPTION';
				$payment['Quantity'] = 1;
					
				$payment['UnitAmount'] = $taxed ? (round($payment['Total'] * (1-self::VAT), 2)) : $payment['Total'];
				$payment['TaxAmount'] = $taxed ? (round($payment['Total'] * self::VAT, 2)) : '0.00';
				
			} else {
				// Not quite sure what falls into this category
				throw new Exception('Unrecognised payment description: ' . $payment['Description']);
			}
			
			$line = $payment;
			unset($line['country_code']);
			unset($line['vat_number']);
			unset($line['plan_name']);
			
			if (!$headersWritten) {
				fputcsv($csv, array_keys($line));
				$headersWritten = true;
			}
			fputcsv($csv, array_values($line));
		}
		
		fclose($csv);
	}
	
}
	
