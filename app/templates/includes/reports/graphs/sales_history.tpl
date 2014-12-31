<div id="right_bar">
	{include file="elements/reports/report_user.tpl"}
	{foldable key="sales_history_help" title="My Sales History Help" extra_class="help"}
	<p>This graph tracks the total cost of opportunities closed by you, for a given 12-month period.</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/reports/graph_actions.tpl" chart_method="salesHistory"}
			{include file="elements/reports/report_index.tpl"}
		</div>
		<div id="chart_graph">
			{$sales_history->outputImg(600, 300)}
		</div>
		<table class="index_table">
			<thead>
				<tr>
					<th>Month</th>
					<th class="numeric">My Total Sales</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Total</th>
					<th class="numeric">{$sales_history_data|@array_sum|pricify}</th>
				</tr>
			</tfoot>
			<tbody>
				{foreach from=$sales_history_data item=item key=key}
				<tr>
					<td>{$key|date_format:"%B %Y"}</td>
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