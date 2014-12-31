{if $user->isAdmin() || ($selected_tags|@count eq 1)}
<div class="edit_delete">
<form action="/tags/action_select" method="post">
{foreach from=$selected_tags item=item name=tag_list}
<input type="hidden" name="tag[]" value="{$item|urlencode}" />
{/foreach}
<label>I want to
<select id="tag_actions" name="tag_action" style="width: auto;">
	{if $selected_tags|@count eq 1}
	<option value="rename">Rename / Merge this tag</option>
	<option value="delete">Delete this tag</option>
	{/if}
	{if $user->isAdmin()}
	{foreach from=$types item=type}
	<option value="delete_{$type}">Delete all these {$type|ucfirst}</option>
	{/foreach}
	{/if}
</select>
</label>
<input class="submit" type="submit" value="Go!" />
</form>
</div>
{/if}