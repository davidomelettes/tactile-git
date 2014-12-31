{if $flash->hasErrors()}
	{flash}
{else}
<form action="/people/save/" class="side_form">
	{with model=$Person tags='none'}
	<fieldset class="sidebar">
		{input type="hidden" attribute="organisation_id" value=$smarty.get.id}
		{input type="text" attribute="firstname"}
		{input type="text" attribute="surname"}
	</fieldset>
	<fieldset class="sidebar">
	{with alias="phone"}
		{input type="hidden" attribute="id" value=""}
		{input type="text" attribute="contact" label="Phone"}
	{/with}
	{with alias="email"}
		{input type="hidden" attribute="id" value=""}
		{input type="text" attribute="contact" label="Email"}
	{/with}
	</fieldset>
	<fieldset class="sidebar save">
		<div class="more">
			<a href="/people/new/?organisation_id={$smarty.get.id}">More Options</a>
		</div>
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