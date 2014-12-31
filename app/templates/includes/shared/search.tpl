{if $items|@count}
<p><span class="sprite"><strong>{if $items->num_pages > 1}First {$items->per_page} {/if}Matches Found:</strong></span></p>
<ul>
	{foreach name=items item=item from=$items}
		<li id="item_{$item->id}"><span class="sprite{if $type} sprite-{$type}{/if}">{$item->$field}</span></li>
	{/foreach}
</ul>
{/if}
