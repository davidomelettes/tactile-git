<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable key="api_access_help" title="API Access" extra_class="help"}
		<p>Full documentation for how to use Tactile CRM's <acronym title="Application Programming Interface">API</acronym> can be found <a href="http://www.tactilecrm.com/api">on the help pages</a>.</p>
		<p>Once you have enabled the API for your users, you can get your <a href="/preferences/keys/">API Token here</a>.</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Tactile CRM API Access</h2>
		</div>
		<form action="/api/setup" method="post" class="saveform" id="api_setup_form">
			<div class="content_holder">
				<fieldset>
					<h3>API Access is {if $api_enabled}Enabled{else}Disabled{/if}</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						{if $api_enabled}
						<p>You can get your User's <strong>API Token</strong> from your <a href="/preferences/keys/">User Preferences page</a>.</p>
						<p>Disabling API Access will disable and <strong>delete the API tokens</strong> of all your account's users.</p>
						<p>Users will have to generate new tokens if API access is enabled again in the future.</p>
						{else}
						<p>Enabling API Access will allow Users to generate an API token to use the service.</p>
						{/if}
					</div>
					<div class="content">
						<div class="row">
							<label>API Access</label>
							<input class="required submit" type="submit" id="api_access" name="api_access" value="{if $api_enabled}Disable API{else}Enable API{/if}" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>
