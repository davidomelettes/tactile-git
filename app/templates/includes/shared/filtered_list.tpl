<ul class="autocomplete_results">
	{foreach name=items item=item from=$items}
		<li id="item_{$item->id}">{$item->$field}</li>
	{/foreach}
</ul>