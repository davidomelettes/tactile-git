<div id="right_bar">
	{include file="elements/reports/report_user.tpl"}
	{foldable key="sales_help" title="Sales Report Help" extra_class="help"}
	<p>This shows you a break down of all won opportunities over the chosen period.</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/reports/report_index.tpl"}
		</div>
		<table id="report_table" class="index_table">
			<thead>
				<tr>
					<th>Organisation</th>
					<th>Opportunity</th>
					{if $report_length eq '21 days'}
					<th class="numeric">7 Days</th>
					<th class="numeric">14 Days</th>
					<th class="numeric">21 Days</th>
					{elseif $report_length eq '90 days'}
					<th class="numeric">30 Days</th>
					<th class="numeric">60 Days</th>
					<th class="numeric">90 Days</th>
					{elseif $report_length eq '9 months'}
					<th class="numeric">3 Months</th>
					<th class="numeric">6 Months</th>
					<th class="numeric">9 Months</th>
					{else}
					<th class="numeric">4 Months</th>
					<th class="numeric">8 Months</th>
					<th class="numeric">12 Months</th>
					{/if}
					<th class="numeric">Close Date</th>
				</tr>
			</thead>
			<tbody>
				{assign var="interval_one" value="0"}
				{assign var="interval_two" value="0"}
				{assign var="interval_three" value="0"}
				{foreach from=$sales_report_data item=item key=key}
				<tr>
					{if $item.organisation_id}
						<td><a href="/organisations/view/{$item.organisation_id}">{$item.organisation}</a></td>
					{else}
						<td>&nbsp;</td>
					{/if}
					<td><a href="/opportunities/view/{$item.opportunity_id}">{$item.opportunity}</a></td>
					<td class="numeric">{if $item.interval_one eq 0}-{else}{$item.interval_one|pricify}{assign var="interval_one" value="`$interval_one+$item.interval_one`"}{/if}</td>
					<td class="numeric">{if $item.interval_two eq 0}-{else}{$item.interval_two|pricify}{assign var="interval_two" value="`$interval_two+$item.interval_two`"}{/if}</td>
					<td class="numeric">{if $item.interval_three eq 0}-{else}{$item.interval_three|pricify}{assign var="interval_three" value="`$interval_three+$item.interval_three`"}{/if}</td>
					<td class="numeric">{$item.enddate|date_format}</td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="6">No data</td>
				</tr>
				{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<th class="numeric" colspan="2">&nbsp;</th>
					{if $report_length eq '21 days'}
					<th class="numeric">7 Days</th>
					<th class="numeric">14 Days</th>
					<th class="numeric">21 Days</th>
					{elseif $report_length eq '90 days'}
					<th class="numeric">30 Days</th>
					<th class="numeric">60 Days</th>
					<th class="numeric">90 Days</th>
					{elseif $report_length eq '9 months'}
					<th class="numeric">3 Months</th>
					<th class="numeric">6 Months</th>
					<th class="numeric">9 Months</th>
					{else}
					<th class="numeric">4 Months</th>
					<th class="numeric">8 Months</th>
					<th class="numeric">12 Months</th>
					{/if}
					<th class="numeric">All</th>
				</tr>
				<tr class="subtotals">
					<th class="numeric" colspan="2">Sales Total</th>
					<th class="numeric">{$interval_one|pricify}</th>
					<th class="numeric">{$interval_two|pricify}</th>
					<th class="numeric">{$interval_three|pricify}</th>
					<th class="numeric">{$interval_one+$interval_two+$interval_three|pricify}</th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
