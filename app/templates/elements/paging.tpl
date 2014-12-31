<div class="paging">
	{if $add}
	<div class="add tall">
		{if $add_disallowed}
		<span class="add_disallowed" title="Adding will take you over your account limit">Add New</span>
		{else}	
		<a href="/{$for|plural}/new/" class="add_{$add} new_link action">Add New</a>
		{/if}
	</div>
	{/if}
	<div class="first tall">
		{if $cur_page <= 2}
		<span class="paging_button_holder paging_first">
			<img src="/graphics/tactile/paging/paging_first_off.png" alt="first" />
		</span>
		{else}
		<a href="/{$for|plural}/{"$action/"|replace:'index/':''}?page=1{if $current_query}&amp;{$current_query}{/if}" class="paging_first paging_link">
			<img src="/graphics/tactile/paging/paging_first.png" alt="first" />
		</a>
		{/if}
	</div>
	<div class="prev tall">
		{if $cur_page <= 1}
		<span class="paging_button_holder paging_previous">
			<img src="/graphics/tactile/paging/paging_previous_off.png" alt="first" />
		</span>
		{else}
		<a href="/{$for|plural}/{"$action/"|replace:'index/':''}?page={$cur_page-1}{if $current_query}&amp;{$current_query}{/if}" class="paging_previous paging_link">
			<img src="/graphics/tactile/paging/paging_previous.png" alt="previous" />
		</a>
		{/if}
	</div>
	<div class="details tall">
		<span class="paging_menu_button">
			<span class="pages">{if $num_pages > 0}Page {$cur_page} of {$num_pages}{else}No results{/if}</span><span class="menu">&nbsp;</span>
		</span>
		<div>
			<form method="get" action="/{$for|plural}/{"$action/"|replace:'index/':''}?{$current_query}">
				<ul class="round-all">
					{if $num_records}<li class="results">{$num_records|number_format} {$for|plural|ucfirst}</li>{/if}
					{if $num_pages > 0}<li class="jump"><label><select name="page">{section name=pages loop=$num_pages}<option value="{$smarty.section.pages.iteration}"{if $cur_page == $smarty.section.pages.iteration} selected="selected"{/if}>{$smarty.section.pages.iteration}</option>{/section}</select>Jump to page:</label></li>{/if}
					{if $sort_order eq 'alphabetical'}
					<li class="jump_az"><label><select name="letter"{if $sort_field eq 'p.firstname'} class="firstname"{/if}><option value="">--</option>{section name=letters loop=26}<option value="{$smarty.section.letters.iteration+64|chr}">{$smarty.section.letters.iteration+64|chr}</option>{/section}</select>Jump to letter:</label></li>
					{/if}
					<li class="perpage"><label><select name="perpage">{foreach from=$perpage_options item=option}<option value="{$option}"{if $option == $perpage} selected="selected"{/if}>{$option}</option>{/foreach}</select>Items per page:</label></li>
				</ul>
			</form>
		</div>
	</div>
	<div class="next tall">
		{if $cur_page == $num_pages or $num_pages eq 0}
		<span class="paging_button_holder paging_next">
			<img src="/graphics/tactile/paging/paging_next_off.png" alt="next" />
		</span>
		{else}
		<a href="/{$for|plural}/{"$action/"|replace:'index/':''}?page={$cur_page+1}{if $current_query}&amp;{$current_query}{/if}" class="paging_next paging_link">
			<img src="/graphics/tactile/paging/paging_next.png" alt="next" />
		</a>
		{/if}
	</div>
	<div class="last tall">
		{if $cur_page >= $num_pages-1  or $num_pages eq 0}
		<span class="paging_button_holder paging_last">
			<img src="/graphics/tactile/paging/paging_last_off.png" alt="last" />
		</span>
		{else}
		<a href="/{$for|plural}/{"$action/"|replace:'index/':''}?page={$num_pages}{if $current_query}&amp;{$current_query}{/if}" class="paging_last paging_link">
			<img src="/graphics/tactile/paging/paging_last.png" alt="last" />
		</a>
		{/if}
	</div>
</div>
