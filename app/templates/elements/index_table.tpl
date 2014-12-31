<form action="/{$for}/mass_action" method="post">
<table id="{$for}_index" class="index_table">
	<thead>
		<tr>
			<th class="cb master"><input type="checkbox" class="checkbox" /></th>
			{include file="elements/indexes/`$for`_header.tpl"}
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th> </th>
			{include file="elements/indexes/`$for`_header.tpl"}
		</tr>
	</tfoot>
	<tbody>
		{foreach from=$index_collection item=model}
		<tr id="{$model->get_name()|strtolower}_{$model->id}">
			<td class="cb"><input type="checkbox" class="checkbox" name="ids[]" value="{$model->id}" /></td>
			{include file="elements/indexes/$for.tpl"}
		</tr>
		{foreachelse}
		<tr>
			<td colspan="9">No {$for|ucfirst} to show.</td>
		</tr>
		{/foreach}
	</tbody>
</table>
</form>