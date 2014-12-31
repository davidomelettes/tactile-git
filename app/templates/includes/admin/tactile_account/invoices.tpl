<div id="right_bar">
	{foldable key="invoices" title="Invoice Information"}
		{if $cost_per_month > 0}<p><strong>Your next invoice date will be {$account_repeat|date_format}</strong> and a receipt for the payment will be <strong>sent directly to {$account_email}</strong> which is the account email we have on file.</p>{/if}
		<p>If you would like a copy invoice resent, please click on the Invoice ID and our accounts team will process it for you.</p>
	{/foldable}
</div>
<div id="the_page" class="account_page">
	<div class="index_holder">
		<div id="page_title">
			<h2>Your Invoices</h2>
		</div>
		<table id="report_table" class="index_table">
			<thead>
				<tr>
					<th>Date</th>
					<th>Invoice ID</th>
					<th>Details</th>
					<th class="numeric">Invoice Total</th>
				</tr>
			</thead>
			<tbody>
				{foreach item=period from=$invoices}
					<tr>
						<td>{$period.day} {$period.month|to_month} {$period.year}</td>
						<td><a class="invoice_link" id="{$period.xero_invoice_id}">{$period.xero_invoice_id}</td>
						<td>{$period.description|replace:'@ ':'at &pound;'}</td>
						<td class="numeric">&pound;{$period.amount|pricify}</td>
					</tr>
				{foreachelse}
					<tr>
						<td colspan="4">You don't currently have any invoices.</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
