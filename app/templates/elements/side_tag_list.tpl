{foldable key=$key  title="Tag List"}
	{if !$selected_tags}
	<p>Choose a tag to filter on:</p>
	{else}
	<p>Further restrict your search, or <a href=/{$type}/ class="action">Start Over</a></p>
	{/if}
	<div class="side_tag_list multiple">
	{foreach name=tags item=tag from=$all_tags}
		{if $selected_tags && $tag|in_array:$selected_tags}
			<div class="tag selected"><span>{$tag|escape}</span></div>
		{else}
			<div class="tag">{$size}<a href="/{$type}/by_tag/?{$current_query}&amp;tag[]={$tag|urlencode}">{$tag|escape}</a></div>
		{/if}
	{foreachelse}
		<span>No tags yet</span>
	{/foreach}
	</div>
	{if $selected_tags}
	<p>Looking for more than just {$type|ucfirst}?</p>
	<p><a href="/tags/by_tag/?{$current_query}">Search Everything</a>.</p>
	{/if}
{/foldable}