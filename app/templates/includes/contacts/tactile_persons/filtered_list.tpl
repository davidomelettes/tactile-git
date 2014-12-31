{if $recent|@count}
<p><span class="sprite"><strong>Recently Viewed:</strong></span></p>
<ul>
	{foreach name=recent item=person from=$recent}
		<li{if $smarty.foreach.recent.last} class="recent_last"{/if} id="item_{$person->id}"><span class="sprite sprite-person">{$person->fullname}{if $person->organisation_id}<span id="organisation_{$person->organisation_id}" class="informal"> ({$person->organisation})</span>{/if}</span></li>
	{/foreach}
</ul>
{/if}
{if $items|@count}
<p><span class="sprite"><strong>{if $items->num_pages > 1}First {$items->per_page} {/if}Matches Found:</strong></span></p>
<ul>
	{foreach name=items item=person from=$items}
		<li id="item_{$person->id}"><span class="sprite sprite-person">{$person->fullname}{if $person->organisation_id}<span id="organisation_{$person->organisation_id}" class="informal"> ({$person->organisation})</span>{/if}</span></li>
	{/foreach}
</ul>
{/if}
