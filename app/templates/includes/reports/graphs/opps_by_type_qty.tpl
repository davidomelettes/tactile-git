<div id="right_bar">
	{include file="elements/reports/report_user.tpl"}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/reports/graph_actions.tpl" chart_method="oppsByTypeQty"}
			{include file="elements/reports/report_index.tpl"}
		</div>
		<div id="chart_graph">
		{if $opps_by_type_qty->hasData()}
			{$opps_by_type_qty->outputImg(600, 300)}
		{else}
			{if $show_user_box}
				<p>There are currently no opportunities that match your criteria - try another user.</p>
			{else}
				<p>No data to plot.</p>
			{/if}
		{/if}
		</div>
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
					<th class="numeric">{$opps_by_type_qty_data|@array_sum}</th>
				</tr>
			</tfoot>
			<tbody>
				{foreach from=$opps_by_type_qty_data item=item key=key}
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