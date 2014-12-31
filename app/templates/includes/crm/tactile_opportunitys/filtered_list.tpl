{if $recent|@count}
<p><span class="sprite"><strong>Recently Viewed:</strong></span></p>
<ul>
	{foreach name=items item=opp from=$recent}
		<li id="item_{$opp->id}"><span class="sprite sprite-opportunity">{$opp->name}</span></li>
	{/foreach}
</ul>
{/if}
{if $items|@count}
<p><span class="sprite"><strong>{if $items->num_pages > 1}First {$items->per_page} {/if}Matches Found:</strong></span></p>
<ul>
	{foreach name=items item=opp from=$items}
		<li id="item_{$opp->id}"><span class="sprite sprite-opportunity">{$opp->name}</span></li>
	{/foreach}
</ul>
{/if}
