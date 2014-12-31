<div id="right_bar">
	{include file="elements/reports/report_index.tpl"}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/reports/graph_actions.tpl" chart_method="oppTypeByCost"}
			<h2>Opportunity Type by Cost</h2>
		</div>
		{if $opp_type_by_cost->hasData()}
		<div id="chart_graph">{$opp_type_by_cost->outputImg(600, 300)}</div>
		{else}
		<p>No data to plot.</p>
		{/if}
		<table class="index_table">
			<thead>
				<tr>
					<th>Type</th>
					<th class="numeric">Total Cost</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Total</th>
					<th class="numeric">{$opp_type_by_cost_data|@array_sum|pricify}</th>
				</tr>
			</tfoot>
			<tbody>
				{foreach from=$opp_type_by_cost_data item=item key=key}
				<tr>
					<td>{$key}</td>
					<td class="numeric">{$item|pricify}</td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="2">No data</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>