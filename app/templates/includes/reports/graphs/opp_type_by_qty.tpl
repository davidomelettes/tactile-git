<div id="right_bar">
	{include file="elements/reports/report_user.tpl"}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/reports/graph_actions.tpl" chart_method="oppTypeByQty"}
			{include file="elements/reports/report_index.tpl"}
		</div>
		{if $opp_type_by_qty->hasData()}
		<div id="chart_graph">{$opp_type_by_qty->outputImg(600, 300)}</div>
		{else}
		<p>No data to plot.</p>
		{/if}
		<table class="index_table">
			<thead>
				<tr>
					<th>Type</th>
					<th class="numeric">Quantity</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Total</th>
					<th class="numeric">{$opp_type_by_qty_data|@array_sum}</th>
				</tr>
			</tfoot>
			<tbody>
				{foreach from=$opp_type_by_qty_data item=item key=key}
				<tr>
					<td>{$key}</td>
					<td class="numeric">{$item}</td>
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