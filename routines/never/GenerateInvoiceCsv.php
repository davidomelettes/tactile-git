<?php

class GenerateInvoiceCsv extends EGSCLIApplication
{
	const PREMIUM_PER_USER = 6.00;
	const ENTERPRISE_PER_USER = 12.00;
	const TAX_TYPE_ZERO = 'ZERORATEDOUTPUT';
	const TAX_TYPE_VAT = 'OUTPUT2';
	//const VAT = 0.2;

	const SEND_EMAILS = true;
	const ACCOUNTS_EMAIL_ADDRESS = 'accounts@omelett.es';

	const STARTING_DATE = '2012-01-01 00:00:00';

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
	protected $_vatCountries = array(
		'GB',
		'AT','BE','BG','CY','CZ','DK','EE','FI','FR',
		'DE','EL','HU','IE','IT','LV','LT','LU','MT',
		'NL','PL','PT','RO','SK','SI','ES','SE'
		);
	*/

		protected function _sendEmail(array $payment, $email_address = self::ACCOUNTS_EMAIL_ADDRESS)
		{
			$mail = new Omelette_Mail('invoice');

			$mail->getMail()->setSubject("Your Receipt (".$payment['InvoiceNumber'].")");
			$mail->getMail()->setFrom(TACTILE_EMAIL_FROM, TACTILE_EMAIL_NAME);

			$mail->getView()->set('firstname', trim($payment['firstname']));
			$mail->getView()->set('surname', trim($payment['surname']));
			$mail->getView()->set('company', trim($payment['ContactName']));
			$mail->getView()->set('site_address', $payment['site_address']);
			$mail->getView()->set('auth_code', $payment['auth_code']);
			$mail->getView()->set('payment_date', date('j F Y', strtotime($payment['InvoiceDate'])));
			$mail->getView()->set('invoice_number', $payment['InvoiceNumber']);
			$mail->getView()->set('description', $payment['Description']);
			$mail->getView()->set('quantity', $payment['Quantity']);
			$mail->getView()->set('line_amount', '&pound;'.number_format($payment['UnitAmount'], 2));
			$mail->getView()->set('line_total', '&pound;'.number_format($payment['UnitAmount'] * $payment['Quantity'], 2));
			$mail->getView()->set('sub_total', '&pound;'.number_format($payment['Total'] - $payment['TaxAmount'], 2));
			$mail->getView()->set('vat', '&pound;'.number_format($payment['TaxAmount'], 2));
			$mail->getView()->set('vat_rate', $this->getVATForCountry($payment['country_code']) * 100);
			$mail->getView()->set('total', '&pound;'.number_format($payment['Total'], 2));
			$mail->getView()->set('total', '&pound;'.number_format($payment['Total'], 2));
			$mail->getView()->set('country', $payment['POCountry']);

			$mail->getMail()->addTo($email_address);
			$mail->send();

			return true;
		}

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
				'0' as \"TaxAmount\",
				a.site_address,
				a.firstname,
				a.surname,
				r.auth_code
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
			ORDER BY
				r.created ASC
		";

			$this->logger->debug('Fetching Invoices...');
			$payments = $db->getArray($sql);
			if (!is_array($payments)) {
				throw new Exception('No results returned: ' . $db->ErrorMsg());
			}
			$this->logger->debug(count($payments) . " invoices fetched");
			foreach ($payments as $payment) {
				$this->logger->debug(sprintf('Processing invoice #%s (country: %s; amount: %s)', $payment['InvoiceNumber'], $payment['country_code'], $payment['Total']));
					
				// Do we tax them?
				$taxed = false;
				$taxRate = $this->getVATForCountry($payment['country_code'], $payment['InvoiceDate']);
				if ($taxRate > 0) {
				    $taxed = true;
				    $payment['TaxType'] = self::TAX_TYPE_VAT;
				}
				/*
				if (in_array($payment['country_code'], $this->_vatCountries)) {
					// In the UK, or are in a taxable country and have given us a VAT number
					$taxed = true;
					$payment['TaxType'] = self::TAX_TYPE_VAT;
				}
				*/
					
				// Sort descriptions and amounts out
				if (preg_match('/\(3[01] days\)/', $payment['Description'])) {
					// We are charging for a full month's subscription
					$payment['Description'] = 'Tactile CRM ' . $payment['plan_name'] . ' Subscription (per user)';

					if ($payment['plan_name'] === 'Premium') {
						// Divide total by per-user cost for qty
						$payment['Quantity'] = intval($payment['Total'] / self::PREMIUM_PER_USER);
							
						// Adjust amounts according to taxation
						if ($taxed) {
							$payment['UnitAmount'] = round(self::PREMIUM_PER_USER / (1 + $taxRate), 2);
							$payment['TaxAmount'] = round($payment['Total'] - ($payment['UnitAmount'] * $payment['Quantity']), 2);
						} else {
							$payment['UnitAmount'] = self::PREMIUM_PER_USER;
							$payment['TaxAmount'] = '0.00';
						}
							
					} else {
						// Divide total by per-user cost for qty
						$payment['Quantity'] = intval($payment['Total'] / self::ENTERPRISE_PER_USER);
							
						// Adjust amounts according to taxation
						if ($taxed) {
							$payment['UnitAmount'] = round(self::ENTERPRISE_PER_USER / (1 + $taxRate), 2);
							$payment['TaxAmount'] = round($payment['Total'] - ($payment['UnitAmount'] * $payment['Quantity']), 2);
						} else {
							$payment['UnitAmount'] = self::ENTERPRISE_PER_USER;
							$payment['TaxAmount'] = '0.00';
						}
					}

				} elseif (preg_match('/@/', $payment['Description'])) {
					// Pro-rata charge for purchasing users
					$details = explode('@', $payment['Description']);
					//$users = trim(str_replace('users', '', $details[0]));
					if (!preg_match('/\d+/', $details[0], $m)) {
						throw new Exception('Failed to extract user quantity from string: ' . $details[0]);
					}
					$users = $m[0];
					preg_match('/\(.+\)/', $details[1], $days);
					$days = str_replace('(', '', str_replace(')', '', $days[0]));

					$payment['Description'] = 'Tactile CRM Upgrade (per user) - Pro Rata ' . $days;
					$payment['Quantity'] = $users;

					if ($taxed) {
						$payment['UnitAmount'] = round($payment['Total'] / (1 + $taxRate) / $users, 2);
						$payment['TaxAmount'] = round($payment['Total'] - ($payment['UnitAmount'] * $payment['Quantity']), 2);
					} else {
						$payment['UnitAmount'] = round($payment['Total'] / $users, 2);
						$payment['TaxAmount'] = '0.00';
					}

				} elseif (preg_match('/Changing plan/', $payment['Description'])) {
					// Plan upgrade
					if ($payment['plan_name'] === 'Premium') {
						// Upgrading from Solo to Premium
						//$users = trim(str_replace('users', '', str_replace('changing plan from solo to premium with', '', $payment['Description'])));
						if (!preg_match('/\d+/', $payment['Description'], $m)) {
							throw new Exception('Failed to extract user quantity from string: ' . $payment['Description']);
						}
						$users = $m[0];
							
						$payment['Description'] = 'Tactile CRM Upgrade (per user)';
						$payment['Quantity'] = $users;
							
						if ($taxed) {
							$payment['UnitAmount'] = round(self::PREMIUM_PER_USER / (1 + $taxRate), 2);
							$payment['TaxAmount'] = round($payment['Total'] - ($payment['UnitAmount'] * $payment['Quantity']), 2);
						} else {
							$payment['UnitAmount'] = self::PREMIUM_PER_USER;
							$payment['TaxAmount'] = '0.00';
						}
							
					} else {
						// Upgrading from Solo/Premium to Enterprise
						$payment['Description'] = 'Tactile CRM Enterprise Upgrade';
						$payment['Quantity'] = 1;
						
						if ($taxed) {
							$payment['UnitAmount'] = round($payment['Total'] / (1 + $taxRate), 2);
							$payment['TaxAmount'] = round($payment['Total'] - ($payment['UnitAmount'] * $payment['Quantity']), 2);
						} else {
							$payment['UnitAmount'] = $payment['Total'];
							$payment['TaxAmount'] = '0.00';
						}
					}

				} elseif (empty($payment['Description'])) {
					// Empty description
					$payment['Description'] = 'TACTILE CRM SUBSCRIPTION UPGRADE';
					$payment['Quantity'] = 1;
					
					if ($taxed) {
						$payment['UnitAmount'] = round($payment['Total'] / (1 + $taxRate), 2);
						$payment['TaxAmount'] = round($payment['Total'] - ($payment['UnitAmount'] * $payment['Quantity']), 2);
					} else {
						$payment['UnitAmount'] = $payment['Total'];
						$payment['TaxAmount'] = '0.00';
					}

				} elseif (preg_match('/Business/', $payment['Description'])) {
					// Old Business account
					$payment['Description'] = 'TACTILE CRM BUSINESS SUBSCRIPTION';
					$payment['Quantity'] = 1;
						
					if ($taxed) {
						$payment['UnitAmount'] = round($payment['Total'] / (1 + $taxRate), 2);
						$payment['TaxAmount'] = round($payment['Total'] - ($payment['UnitAmount'] * $payment['Quantity']), 2);
					} else {
						$payment['UnitAmount'] = $payment['Total'];
						$payment['TaxAmount'] = '0.00';
					}

				} elseif (preg_match('/Premier/', $payment['Description'])) {
					// Old Premier account
					$payment['Description'] = 'TACTILE CRM PERMIER SUBSCRIPTION';
					$payment['Quantity'] = 1;
						
					if ($taxed) {
						$payment['UnitAmount'] = round($payment['Total'] / (1 + $taxRate), 2);
						$payment['TaxAmount'] = round($payment['Total'] - ($payment['UnitAmount'] * $payment['Quantity']), 2);
					} else {
						$payment['UnitAmount'] = $payment['Total'];
						$payment['TaxAmount'] = '0.00';
					}

				} elseif (preg_match('/SME/', $payment['Description'])) {
					// Old SME account
					$payment['Description'] = 'TACTILE CRM SME SUBSCRIPTION';
					$payment['Quantity'] = 1;
						
					if ($taxed) {
						$payment['UnitAmount'] = round($payment['Total'] / (1 + $taxRate), 2);
						$payment['TaxAmount'] = round($payment['Total'] - ($payment['UnitAmount'] * $payment['Quantity']), 2);
					} else {
						$payment['UnitAmount'] = $payment['Total'];
						$payment['TaxAmount'] = '0.00';
					}

				} elseif (preg_match('/Micro/', $payment['Description'])) {
					// Old Micro account
					$payment['Description'] = 'TACTILE CRM MICRO SUBSCRIPTION';
					$payment['Quantity'] = 1;
						
					if ($taxed) {
						$payment['UnitAmount'] = round($payment['Total'] / (1 + $taxRate), 2);
						$payment['TaxAmount'] = round($payment['Total'] - ($payment['UnitAmount'] * $payment['Quantity']), 2);
					} else {
						$payment['UnitAmount'] = $payment['Total'];
						$payment['TaxAmount'] = '0.00';
					}

				} else {
					// Not quite sure what falls into this category
					throw new Exception('Unrecognised payment description: ' . $payment['Description']);
				}
					
				$line = $payment;
				unset($line['country_code']);
				unset($line['vat_number']);
				unset($line['plan_name']);
				unset($line['site_address']);
				unset($line['firstname']);
				unset($line['surname']);
				unset($line['auth_code']);
					
				if (!$headersWritten) {
					fputcsv($csv, array_keys($line));
					$headersWritten = true;
				}
				fputcsv($csv, array_values($line));
					
				if (self::SEND_EMAILS) {
					try {
						$this->_sendEmail($payment);
					} catch (Exception $e) {
						$this->logger->err($e->getMessage());
						exit;
					}
				}
			}

			fclose($csv);
		}

}
