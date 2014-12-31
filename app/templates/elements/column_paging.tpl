<div class="paging"{if $num_pages[$for] < 2} style="display: none;"{/if}>
	<div class="paging_loading" style="display: none;"><span>Loading...</span></div>
	<div class="{$for}_paging_details paging_details">
		{if $num_pages[$for] > 0}
		<span class="not_empty">Page {$cur_page[$for]} of {$num_pages[$for]}</span>
		{else}
		<span class="empty">No items</span>
		{/if}
	</div>
	{if $cur_page[$for] <= 2}
	<span class="paging_button_holder">
	<img src="/graphics/tactile/paging/paging_first_off.png" alt="first" />
	</span>
	{else}
	<a href="/tags/{"$action/"|replace:'index/':''}?page=1{if $current_query[$for]}&amp;{$current_query[$for]}{/if}" class="{$for}_paging_first paging_link">
	<img src="/graphics/tactile/paging/paging_first.png" alt="first" />
	</a>
	{/if}
	{if $cur_page[$for] <= 1}
	<span class="paging_button_holder">
	<img src="/graphics/tactile/paging/paging_previous_off.png" alt="first" />
	</span>
	{else}
	<a href="/tags/{"$action/"|replace:'index/':''}?page={$cur_page[$for]-1}{if $current_query[$for]}&amp;{$current_query[$for]}{/if}" class="{$for}_paging_previous paging_link">
	<img src="/graphics/tactile/paging/paging_previous.png" alt="previous" />
	</a>
	{/if}
	{if $cur_page[$for] == $num_pages[$for] or $num_pages[$for] eq 0}
	<span class="paging_button_holder">
	<img src="/graphics/tactile/paging/paging_next_off.png" alt="next" />
	</span>
	{else}
	<a href="/tags/{"$action/"|replace:'index/':''}?page={$cur_page[$for]+1}{if $current_query[$for]}&amp;{$current_query[$for]}{/if}" class="{$for}_paging_next paging_link">
	<img src="/graphics/tactile/paging/paging_next.png" alt="next" />
	</a>
	{/if}
	{if $cur_page[$for] >= $num_pages[$for]-1  or $num_pages[$for] eq 0}
	<span class="paging_button_holder">
	<img src="/graphics/tactile/paging/paging_last_off.png" alt="last" />
	</span>
	{else}
	<a href="/tags/{"$action/"|replace:'index/':''}?page={$num_pages[$for]}{if $current_query[$for]}&amp;{$current_query[$for]}{/if}" class="{$for}_paging_last paging_link">
	<img src="/graphics/tactile/paging/paging_last.png" alt="last" />
	</a>
	{/if}
</div>
