<div id="system_navbar">
	<ul id="system_nav">
		<li class="l1 logout">
			<a href="/logout/" title="You are logged in as {$current_user->username|escape}">Logout &raquo;</a>
		</li>
		{if $current_user->isAdmin()}
		<li class="l1{if $_is_admin_area} on{/if}">
			<a class="context" href="/admin/">Admin</a>
			<div class="detail">
				<div class="bubble">
					<ul>
						<li class="inline">Manage <a href="/users/">Users</a>, <a href="/groups/">Groups</a> &amp; <a href="/permissions/">permissions</a></li>
						<li><a href="/account/">Change your account details</a></li>
						<li><a href="/appearance/">Change our colours &amp; logo</a></li>
						<li><a href="/customfields/">Set up custom fields</a></li>
						<li><a href="/admin/">More admin options...</a></li>											
					</ul>
				</div>
			</div>
		</li>
		{/if}
		<li class="l1{if $_area eq 'preferences'} on{/if}">
			<a class="context" href="/preferences/">My Preferences</a>
			<div class="detail">
				<div class="bubble">
					<ul>
						<li><a href="/preferences/password/">Change your password</a></li>
						<li><a href="/preferences/dashboard/">Customise the dashboard</a></li>
						<li><a href="/preferences/email/">Email preferences</a></li>
						<li><a href="/preferences/date_time/">Set date/time display</a></li>
						<li><a href="/preferences/keys/">Email dropbox, calendar &amp; API access</a></li>
					</ul>
				</div>
			</div>
		</li>
		<li class="l1 last">
			<a class="context">Help &amp; Support</a>
			<div class="detail">
				<div class="bubble">
					<ul>
						<li><a href="http://help.tactilecrm.com">Visit our Help Site</a></li>
						<li><a href="http://feedback.omelett.es/pages/tactile_crm/">Suggest a feature</a></li>
						<li><a id="help_link">Raise a Support Request</a></li>
					</ul>
				</div>
			</div>
		</li>
	</ul>
	<div id="system_welcome">
		{assign var=user_model value=$current_user->getModel()}
		Hi <a href="/people/view/{$user_model->person_id}"><strong>{$current_user->username|escape}</strong></a>, welcome back to 
		{if !$current_user->isResolveEnabled()}
			<a href="/">Tactile CRM</a>. 
		{else}
			{assign var=resolve_user_account value=$current_user->getAccount()}
			<a class="context" href="/">Tactile CRM</a>
			<div class="detail">
				<div class="bubble">
					<p>Login to:</p>
					<ul>
						<li><a href="http://{$current_user->getUserspace()}.resolverm.com">Resolve RM</a></li>
					</ul>
				</div>
			</div>
		{/if}
	</div>
</div>