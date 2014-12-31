{if $by_accountno|@count}
<p><span class="sprite"><strong>by Account Number:</strong></span></p>
<ul>
	{foreach name=by_accountno item=org from=$by_accountno}
		<li{if $smarty.foreach.recent.last} class="by_accountno_last"{/if} id="item_{$org->id}"><span class="sprite sprite-organisation">{$org->name}</span></li>
	{/foreach}
</ul>
{/if}
{if $recent|@count}
<p><span class="sprite"><strong>Recently Viewed:</strong></span></p>
<ul>
	{foreach name=recent item=org from=$recent}
		<li{if $smarty.foreach.recent.last} class="recent_last"{/if} id="item_{$org->id}"><span class="sprite sprite-organisation">{$org->name}</span></li>
	{/foreach}
</ul>
{/if}
{if $items|@count}
<p><span class="sprite"><strong>{if $items->num_pages > 1}First {$items->per_page} {/if}Matches Found:</strong></span></p>
<ul>
	{foreach name=items item=org from=$items}
		<li id="item_{$org->id}"><span class="sprite sprite-organisation">{$org->name}</span></li>
	{/foreach}
</ul>
{/if}
