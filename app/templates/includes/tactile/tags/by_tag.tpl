<div id="full_page">
	<div class="tag_index_holder">
		<div id="page_title">
			{include file="includes/tactile/tags/actions.tpl" types=$types_with_results}
			<h2>Items Tagged: {foreach name=tags item=tag from=$selected_tags}&ldquo;{$tag|escape}&rdquo;{if !$smarty.foreach.tags.last} &amp; {/if}{/foreach}</h2>
		</div>
		{if $is_import && $current_user->isAdmin()}
		<div class="form_help" id="import_undo">
			<p>To undo this import, you can delete all of the Organisations and/or People below using the controls in the top right.</p>
		</div>
		{/if}
		{if $filter_by|@count > 1}
		<div id="by_tag_filter">
			<span class="restrict_by">Further restrict your search, or <a href="/tags/{if $selected_tags|@count > 1}by_tag/?tag[]={$selected_tags.0|urlencode}{/if}" class="action">Start Over</a>:</span>
			{foreach from=$filter_by item=filter}
			{if $filter.name|in_array:$selected_tags}
			<span class="selected">{$filter.name}</span>
			{else}
			<a class="filter" href="/tags/by_tag/?{foreach from=$selected_tags item=selected}tag[]={$selected|urlencode}&amp;{/foreach}tag[]={$filter.name|urlencode}">{$filter.name}</a>
			{/if}
			{/foreach}
		</div>
		{/if}
		{if $nothing_to_display}
		<p>No items at present.</p>
		{else}
		<ul id="tagged_item_columns">
			{foreach from=$items item=collection key=for}
			{if $collection->count()}
			<li style="width: {$column_width}%;" id="tagged_{$for}_list" class="{if $first eq $for}first{/if} {if $last eq $for}last{/if}">
				<div class="border">
					<div class="head">
						<h2>{$for|ucfirst}</h2>
						<div class="top_paging">
							{include file="elements/column_paging.tpl" for=$for}
						</div>
					</div>
					<div class="content">
						<table class="index_table">
							{if $num_pages[$for] > 1}
							<tfoot>
								<tr>
									<td><div class="bottom_paging">{include file="elements/column_paging.tpl" for=$for}</div></td>
								</tr>
							</tfoot>
							{/if}
							<tbody>
								{foreach from=$collection item=item}
								<tr>
									<td class="{$item_types.$for.class}_name">
										<a href="/{$for}/view/{$item->id}">{$item->name}</a>
									</td>
								</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
					<div style="clear: both;"></div>
				</div>
			</li>
			{/if}
			{/foreach}
		</ul>
		<div style="clear:both;"></div>
		{/if}
	</div>
</div>