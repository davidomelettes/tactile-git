{if $invoices|@count gt 0}
<h4>Invoices</h4>
<ul id="invoices" class="related_list">
	{foreach item=invoice from=$invoices}
	<li>
		{if $invoice->isPartPaid()}
			<span class="part-paid">
		{elseif $invoice->amount eq $invoice->amount_outstanding}
			<span class="unpaid">
		{else}
			<span class="paid">
		{/if}
		#{$invoice->number}</span>
		
		<dl>
			<dt>Date:</dt>
			<dd>{$invoice->getDate($DATE_FORMAT)}</dd><br />
			<dt>Amount:</dt>
			<dd>
				{if $invoice->isPartPaid()}
					{$invoice->amount_outstanding|pricify} of {$invoice->amount|pricify} due.
				{elseif $invoice->amount eq $invoice->amount_outstanding}
					{$invoice->amount_outstanding|pricify} due.
				{else}
					{$invoice->amount|pricify} paid.
				{/if}
			</dd><br />
			<dt>Status:</dt>
			<dd>{$invoice->status}</dd>
		</dl>
	</li>
	{/foreach}
</ul>
{/if}