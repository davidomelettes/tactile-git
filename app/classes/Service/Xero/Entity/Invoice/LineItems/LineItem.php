<?php

class Service_Xero_Entity_Invoice_LineItems_LineItem extends Service_Xero_Entity_Abstract {
	
	const TAX_NONE = 'NONE';						// No GST
	const TAX_EXEMPTINPUT = 'EXEMPTINPUT';			// VAT on expenses exempt from VAT (UK only)
	const TAX_INPUT = 'INPUT2';						// GST on expenses
	const TAX_SRINPUT = 'SRINPUT';					// VAT on expenses
	const TAX_ZERORATEDINPUT = 'ZERORATEDINPUT';	// Expense purchased from overseas (UK only)
	const TAX_RRINPUT = 'RRINPUT';					// Reduced rate VAT on expenses (UK Only)
	const TAX_EXEMPTOUTPUT = 'EXEMPTOUTPUT';		// VAT on sales exempt from VAT (UK only)
	const TAX_OUTPUT = 'OUTPUT2';					// GST on sales
	const TAX_SROUTPUT = 'SROUTPUT';				// VAT on sales
	const TAX_ZERORATEDOUTPUT = 'ZERORATEDOUTPUT';	// Sales made from overseas (UK only)
	const TAX_RROUTPUT = 'RROUTPUT';				// Reduced rate VAT on sales (UK Only)
	const TAX_ZERORATED = 'ZERORATED';				// Zero-rated supplies/sales from overseas (NZ Only)
	
	protected $_properties = array(
		'LineItemID',
		'Description',
		'Quantity',
		'UnitAmount',
		'TaxType',
		'TaxAmount',
		'LineAmount',
		'AccountCode',
		'Tracking'
	);
}
