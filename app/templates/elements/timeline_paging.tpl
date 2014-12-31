<div class="paging">
	{if $add}
	{if $add_disallowed}
		<span class="add_disallowed" title="Adding will take you over your account limit">Add New</span>
	{else}	
		<a href="/{$for|plural}/new/" class="add_{$add} new_link action">Add New</a>
	{/if}
	{/if}
	{if $cur_page <= 2}
	<span class="paging_button_holder paging_first">
	<img src="/graphics/tactile/paging/paging_first_off.png" alt="first" />
	</span>
	{else}
	<a href="/{$for|plural}/{"$action/"|replace:'index/':''}?timeline_page=1{if $current_query}&amp;{$current_query}{/if}" class="paging_first paging_link">
	<img src="/graphics/tactile/paging/paging_first.png" alt="first" />
	</a>
	{/if}
	{if $cur_page <= 1}
	<span class="paging_button_holder paging_previous">
	<img src="/graphics/tactile/paging/paging_previous_off.png" alt="first" />
	</span>
	{else}
	<a href="/{$for|plural}/{"$action/"|replace:'index/':''}?timeline_page={$cur_page-1}{if $current_query}&amp;{$current_query}{/if}" class="paging_previous paging_link">
	<img src="/graphics/tactile/paging/paging_previous.png" alt="previous" />
	</a>
	{/if}
	{if $num_pages > 0}
	<span class="paging_details">Page {$cur_page} of {$num_pages}</span>
	{else}
	<span class="paging_details_empty">No items</span>
	{/if}
	{if $cur_page == $num_pages or $num_pages eq 0}
	<span class="paging_button_holder paging_next">
	<img src="/graphics/tactile/paging/paging_next_off.png" alt="next" />
	</span>
	{else}
	<a href="/{$for|plural}/{"$action/"|replace:'index/':''}?timeline_page={$cur_page+1}{if $current_query}&amp;{$current_query}{/if}" class="paging_next paging_link">
	<img src="/graphics/tactile/paging/paging_next.png" alt="next" />
	</a>
	{/if}
	{if $cur_page >= $num_pages-1  or $num_pages eq 0}
	<span class="paging_button_holder paging_last">
	<img src="/graphics/tactile/paging/paging_last_off.png" alt="last" />
	</span>
	{else}
	<a href="/{$for|plural}/{"$action/"|replace:'index/':''}?timeline_page={$num_pages}{if $current_query}&amp;{$current_query}{/if}" class="paging_last paging_link">
	<img src="/graphics/tactile/paging/paging_last.png" alt="last" />
	</a>
	{/if}
</div>
