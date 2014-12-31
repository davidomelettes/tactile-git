{foldable key="graphs" title=$graph_title extra_class="dashboard_graph"}
<a href="{$graph_url}">{if $graph}{$graph->outputImg(268, 133)}{else}<img src="/graphics/tactile/sample_graph.png" alt="Sample Graph" />{/if}</a>
<ul class="sidebar_options">
	<li>
		<p><a href="{$graph_url}" class="sprite sprite-graph">More Graphs</a></p>
	</li>
</ul>
{/foldable}