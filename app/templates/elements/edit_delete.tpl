<div class="edit_delete">
{if $model->get_name() eq 'User'}
{if $model->is_enabled()}
<form action="/{$url}/disable/" method="post" class="disable_form">
{else}
<form action="/{$url}/enable/" method="post" class="enable_form">
{/if}
{else}
<form action="/{$url}/delete/" method="post" class="delete_form">
{/if}
    <fieldset>
        <input type="hidden" value="{if $model->username}{$model->username}{else}{$model->id}{/if}" name="{if $model->username}username{else}id{/if}" />
        {if $current_user->canEdit($model) && !$edit_locked}<a class="action" href="/{$url}/edit/{if $model->username}{$model->username}{else}{$model->id}{/if}" id="edit_{$for}">Edit</a>{else}<span class="grey_edit">Edit</span>{/if} <span class="edit_delete_or">or</span>
        {if $model->get_name() eq 'User'}
        <input class="submit" type="submit" {if $model->is_enabled()}value="Disable"{else}value="Enable"{/if} />
        {elseif $model->get_name() eq 'Person' && $model->isUser()}
        <input class="submit" type="submit" value="Delete" id="delete_{$for}" disabled="disabled" title="You cannot delete a User's Person" />
        {else}
        <input class="submit" type="submit" value="Delete" id="delete_{$for}" {if !$current_user->canDelete($model)}disabled="disabled"{/if} />
        {/if}
    </fieldset>
</form>
{if $model->get_name() eq 'Opportunity' && !$model->is_archived()}
<form action="/{$url}/save/" method="post" class="archive_form">
	<fieldset>
		<input type="hidden" name="Opportunity[id]" value="{$model->id}" />
		<input type="hidden" name="Opportunity[archived]" value="on" />
		<input type="hidden" name="Opportunity[_checkbox_exists_archived]" value="true" />
	    <input class="submit"{if !$current_user->canEdit($model)} disabled="disabled"{/if} type="submit" value="Archive" />
   	</fieldset>
</form>
{/if}
</div>
