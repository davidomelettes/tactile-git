<div class="border">
	<div class="head">
		<h2>{$title}</h2>
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
				{foreach name=tagged_items item=item from=$items}
				<tr>
					<td class="{$class}_name">
						<a href="/{$for}/view/{$item->id}">{$item->name}</a>
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<div style="clear: both;"></div>
</div>