<div id="right_bar">
	{include file="elements/reports/report_user.tpl"}
	{foldable key="pipeline_help" title="Pipeline Report Help" extra_class="help"}
	<p>This shows you a break down of all open opportunities that are still to close (it doesn't show opportunities with an expected close date before the beginning of the current month unless you check the 'Include Old' option above).</p>
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
					<th class="numeric">Probability</th>
					<th class="numeric">30 Days</th>
					<th class="numeric">60 Days</th>
					<th class="numeric">90 Days</th>
					<th class="numeric">90 Days +</th>
					<th class="numeric">Expected Close</th>
				</tr>
			</thead>
			<tbody>
				{assign var="thirtyday" value="0"}
				{assign var="sixtyday" value="0"}
				{assign var="ninetyday" value="0"}
				{assign var="ninetydayplus" value="0"}
				{foreach from=$pipeline_report_data item=item key=key}
				<tr>
					{if $item.organisation_id}
						<td><a href="/organisations/view/{$item.organisation_id}">{$item.organisation}</a></td>
					{else}
						<td>&nbsp;</td>
					{/if}
					<td><a href="/opportunities/view/{$item.opportunity_id}">{$item.opportunity}</a></td>
					<td class="numeric">{$item.probability}</td>
					<td class="numeric">{if $item.thirtydays eq 0}-{else}{$item.thirtydays|pricify}{assign var="thirtyday" value="`$thirtyday+$item.thirtydays`"}{/if}</td>
					<td class="numeric">{if $item.sixtydays eq 0}-{else}{$item.sixtydays|pricify}{assign var="sixtyday" value="`$sixtyday+$item.sixtydays`"}{/if}</td>
					<td class="numeric">{if $item.ninetydays eq 0}-{else}{$item.ninetydays|pricify}{assign var="ninetyday" value="`$ninetyday+$item.ninetydays`"}{/if}</td>
					<td class="numeric">{if $item.ninetydaysplus eq 0}-{else}{$item.ninetydaysplus|pricify}{assign var="ninetydayplus" value="`$ninetydayplus+$item.ninetydaysplus`"}{/if}</td>
					<td class="numeric">{$item.enddate|date_format}</td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="8">No data</td>
				</tr>
				{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<th class="numeric" colspan="3">&nbsp;</th>
					<th class="numeric">30 Days</th>
					<th class="numeric">60 Days</th>
					<th class="numeric">90 Days</th>
					<th class="numeric">90 Days +</th>
					<th class="numeric">All</th>
				</tr>
				<tr class="subtotals">
					<th class="numeric" colspan="3">Total Pipeline</th>
					<th class="numeric">{$thirtyday|pricify}</th>
					<th class="numeric">{$sixtyday|pricify}</th>
					<th class="numeric">{$ninetyday|pricify}</th>
					<th class="numeric">{$ninetydayplus|pricify}</th>
					<th class="numeric">{$thirtyday+$sixtyday+$ninetyday+$ninetydayplus|pricify}</th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
