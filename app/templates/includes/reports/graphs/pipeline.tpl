<div id="right_bar">
	{include file="elements/reports/report_user.tpl"}
	{foldable key="pipeline_help" title="My Pipeline Help" extra_class="help"}
	<p>The pipeline shows all your open opportunities and those that have been closed this month.</p>
	<p>The total value of opportunities in each stage is shown as well as the value 'weighted' by the probability.</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/reports/graph_actions.tpl" chart_method="pipeline"}
			{include file="elements/reports/report_index.tpl"}
		</div>
		<div id="chart_graph">
			{$pipeline->outputImg(600, 300)}
		</div>
		<table class="index_table">
			<thead>
				<tr>
					<th>Sales Stage</th>
					<th class="numeric">Weighted Value</th>
					<th class="numeric">Total Value</th>
				</tr>
			</thead>
			<!--<tfoot>
				<tr>
					<th>Total</th>
					<th class="numeric">{$pipeline_data|@array_sum|pricify}</th>
					<th class="numeric">{$pipeline_data|@array_sum|pricify}</th>
				</tr>
			</tfoot>-->
			<tbody>
				{foreach from=$pipeline_data item=item key=key}
				<tr>
					<td>{$key}</td>
					{foreach from=$item item=cost key=type}
						<td class="numeric">{$cost|pricify}</td>
					{/foreach}
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
