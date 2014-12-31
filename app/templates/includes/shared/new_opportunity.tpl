{if $flash->hasErrors()}
	{flash}
{else}
<form action="/opportunities/save/" class="side_form">
	{with model=$Opportunity tags='none'}
	<fieldset class="sidebar">
		{input type="text" attribute="name"}
		{input type="hidden" attribute="organisation_id"}
		{input type="hidden" attribute="person_id"}
		{if !$Person->id}
			{input type="text" attribute="person"}
		{/if}
		
		{input type="text" attribute="cost" label="Value"}
		{input type="date" attribute="enddate"}
		{select attribute="status_id" label="Sales Stage"}
	</fieldset>
	<fieldset class="sidebar">
		{select attribute="source_id"}
		{select attribute="assigned"}
	</fieldset>
	<fieldset class="sidebar save">
		{strip}
		<div class="more">
		<a href="/opportunities/new/?
			{if $Person->id}person_id={$Person->id}{if $Person->organisation_id}&amp;organisation_id={$Person->organisation_id}{/if}{/if}		
			{if $Organisation->id}organisation_id={$Organisation->id}{/if}
		">More Options</a>
		</div>
		{/strip}
		<div class="cancel_or_save">
			<a href="#" class="cancel">Cancel</a>
			<span class="or"> or </span>
			<input type="submit" value="Save" class="button"/>
		</div>
		<div class="clear"></div>
	</fieldset>
	{/with}
</form>
{/if}