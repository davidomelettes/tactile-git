{if $estimates|@count gt 0}
<h4>Estimates</h4>
<ul id="estimates" class="related_list">
	{foreach item=estimate from=$estimates}
	<li>
		#{$estimate->number}</span>
		
		<dl>
		<dt>Date:</dt>
		<dd>{$estimate->getDate($DATE_FORMAT)}</dd><br />
		<dt>Amount:</dt>
		<dd>{$estimate->amount|pricify}</dd><br />
		<dt>Status:</dt>
		<dd>{$estimate->status}</dd>
	</li>
	{/foreach}
</ul>
{/if}