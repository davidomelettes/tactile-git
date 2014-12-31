{if $search neq ''}
<div id="data_grid_search">
{form controller=$self.controller action="index" notags=true}
	{if $search->hasFields('advanced')}
	<fieldset id="advanced_holder">
		<input type="button" id="show_advanced_search" value="{if !$controller_data.advanced}+{else}-{/if}" />
	</fieldset>
	{/if}
	<fieldset id="basic_search">
		{$search->toHTML('basic')}
	</fieldset>
	<fieldset id="advanced_search" {if !$controller_data.advanced}style="display:none;"{/if}>
		{$search->toHTML('advanced')}
	</fieldset>
	<fieldset id="submit_holder">
		<input type="submit" id="search_submit" value="Search" />
		<input type="submit" id="search_clear" value="Clear" name="Search[clear]"/>
	</fieldset>
{/form}
</div>
{/if}
<div id="data_grid_header" class="clearfix">
{if $num_pages > 0}
<span class="paging">
{if $cur_page >2 }
	{link_first}
{/if}
{if $cur_page >1}
	{link_prev page=$cur_page}
{/if}
{$cur_page} of {$num_pages}
{if $cur_page lt $num_pages}
	{link_next page=$cur_page}
{/if}
{if $cur_page lt ($num_pages-1)}
{link_last}
{/if}
</span>
{/if}
</div>
{assign var=templatemodel value=$collection->getModel()}
{form controller=$self.controller action="index" notags=true}
{php}showtime('before-table');{/php}
{data_table}
	<thead><tr>
		{foreach name=headings item=heading key=fieldname from=$collection->getHeadings()}
		{heading_cell field=$fieldname}
			{$heading}
		{/heading_cell}
		{/foreach}
		{if $data_table_actions}
		<th>&nbsp;</th>
		{/if}
	</tr></thead>
{*	{datatable_body collection=$collection} *}
	
	{foreach name=datagrid item=model from=$collection}
	{grid_row model=$model}
		{foreach name=gridrow item=tag key=fieldname from=$collection->getHeadings()}
		{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection}
			{$model->$fieldname}
		{/grid_cell}
		{/foreach}
	{/grid_row}
	{foreachelse}
		<tr><td colspan="0">No matching records found!</td></tr>
	{/foreach}
	
{/data_table}
{php}showtime('end-table');{/php}
<div id="data_grid_footer" class="clearfix">
{if $num_pages > 0}
<span class="paging">
			{if $cur_page >2 }
				{link_first}
			{/if}
			{if $cur_page >1}
				{link_prev page=$cur_page}
			{/if}
			{$cur_page} of {$num_pages}
			{if $cur_page lt $num_pages}
				{link_next page=$cur_page}
			{/if}
			{if $cur_page lt ($num_pages-1)}
			{link_last}
			{/if}
</span>
{/if}
{if $data_table_actions}
<fieldset id="mass_action">
<label for="data_table_action">All Selected:</label>
<select name="data_table_action">
{html_options options=$data_table_actions}
</select>
<input type="submit" value="Go" />
</fieldset>
{/if}
{/form}
{if $search eq ''}
	{if !$no_ordering}
	{include file="elements/quicksearch.tpl" collection=$collection->getModel()}
	{/if}
{/if}
</div>
<div style="clear: both;">&nbsp;</div>
