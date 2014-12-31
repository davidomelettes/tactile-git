<ul>
	{if $parent->organisation}
	<li><a href="/organisations/view/{$parent->organisation_id}" class="sprite sprite-organisation" style="{if $parent->person || $parent->opportunity || $parent->activity}width: 1px;{else}color: #000;{/if}" title="{$parent->organisation|escape}">{$parent->organisation|escape}</a></li>
	{/if}
	{if $parent->person}
	<li><a href="/people/view/{$parent->person_id}" class="sprite sprite-person{if $parent->organisation} child{/if}" style="{if $parent->opportunity || $parent->activity}width: 1px;{else}color: #000;{/if}" title="{$parent->person|escape}">{$parent->person|escape}</a></li>
	{/if}
	{if $parent->opportunity}
	<li><a href="/opportunities/view/{$parent->opportunity_id}" class="sprite sprite-opportunity{if $parent->person || $parent->organisation} child{/if}" style="{if $parent->activity}width: 1px;{else}color: #000;{/if}" title="{$parent->opportunity|escape}">{$parent->opportunity|escape}</a></li>
	{/if}
	{if $parent->activity}
	<li><a href="/activities/view/{$parent->activity_id}" class="sprite sprite-activity{if $parent->opportunity || $parent->person || $parent->organisation} child{/if}" style="color: #000;" title="{$parent->activity|escape}">{$parent->activity|escape}</a></li>
	{/if}
</ul>