<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{if $accountname}
	{foldable key="freshbooks_import" title="Import FreshBooks Contacts"}
		<p>If you have contacts in FreshBooks that don't exist in Tactile you can:</p>
		<ul class="sidebar_options">
			<li><a class="action sprite sprite-import" href="/import/freshbooks/">Import them in bulk</a></li>
		</ul>
		<p>To connect organisations in Tactile with clients in FreshBooks, look for the <strong>Invoices</strong> box at the side of the organisation's page.</p>
	{/foldable}
	{/if}
	{foldable key="freshbooks_help" title="What is FreshBooks?" extra_class="help"}
	<p>
		<a href="https://omelettes.freshbooks.com/signup/" target="_blank">FreshBooks</a> is a hosted service, like Tactile, that lets you deal with invoicing and time tracking.
	</p>
	<p>
		By linking your Tactile account with your FreshBooks account you'll be able to see the details of your invoices right here inside Tactile.
	</p>
	<p>
		They provide free accounts so if you haven't already, you might want to <a href="https://omelettes.freshbooks.com/signup/">give it a try</a>.
	</p>
	{if $show_coupon}
	<p>
		We've also got a <strong>$20 discount</strong> code you can use until the 1st October 2009 if you want to start using it properly. Just enter <strong>tactile09</strong> when you upgrade your FreshBooks plan.
	</p>
	{/if}
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Setup FreshBooks Integration</h2>
		</div>
		<form action="/freshbooks/setup" method="post" class="saveform" id="freshbooks_setup_form">
			<div class="content_holder">
				<fieldset>
					<h3>FreshBooks Account Details</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						{if $accountname}
						<p>If you regenerate your <strong>Admin Access Token</strong> you will need to update the value below with the one from <a href="http://{$accountname}.freshbooks.com">your FreshBooks account</a>.</p>
						<p>Don't forget you can also <strong><a href="/import/freshbooks/">import organisations and people from FreshBooks</a></strong> to quickly link them with Tactile CRM.</p>
						{else}
						<p>To integrate with FreshBooks we'll need your <strong>Account Name</strong> and <strong>Admin Access Token</strong>.</p>
						<p>Before you start, you'll need to have <strong>enabled API Access</strong> via the "Settings" page <a href="http://www.freshbooks.com/?ref=b0784e7d114183-1" target="_blank">within FreshBooks</a>.</p>
						{/if}
					</div>
					<div class="content">
						<div class="right"><a href="https://omelettes.freshbooks.com/signup/" target="_blank"><img src="/graphics/3rd_party/freshbooks.gif" alt="FreshBooks Logo" /></a></div>
						<div style="position: relative;">
							<div class="row">
								<label for="accountname">Account Name</label>
								{if $accountname}
								<span class="fb_account_name false_input" id="accountname">{$accountname|escape}.freshbooks.com</span>
								{else}
								<input class="required" type="text" id="accountname" name="accountname" /><span style="position: absolute; left: 405px; top: 5px; font-size: 1.2em;">.freshbooks.com</span>
								{/if}
							</div>
						</div>
						<div class="row">
							<label for="token">Admin Access Token</label>
							<input class="required" type="text" id="token" name="token" value="{$token}"/>
						</div>
					</div>
				</fieldset>
				<fieldset class="prefs_save">
					<div class="content">
						<div class="row">
							<input type="submit" class="submit" value="{if $accountname}Update Token{else}Connect{/if}" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
		{if $accountname}
		<form action="/freshbooks/reset" method="post" class="saveform delete_form" id="freshbooks_reset_form">
			<div class="content_holder">
				<fieldset class="prefs_save">
					<h3>Unlink FreshBooks Account</h3>
					<div class="content">
						<p>If you want to unlink Tactile from FreshBooks, click this button.</p>
						<div class="row">
							<input class="submit" type="submit" value="Reset" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
		{/if}
	</div>
</div>
