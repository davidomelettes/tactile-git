<div id="right_bar">
	{foldable key="admin_overview_help" title="Admin Help" extra_class="help"}
	<p>Admins may use these controls to customise Tactile CRM to their specific requirements.</p>
	<p>Further help is available within each section.</p>
	{/foldable}
	{foldable key="tactilreferal" title="Referral Program" extra_class="unfoldable"}
		{if !$referral_date}
		<form action="/account/referrals/" method="post">
			<ul class="sidebar_options">
				<li><label><input type="checkbox" class="checkbox" name="tactile_referral_agree" />
				I agree to the <a href="http://www.tactilecrm.com/terms-referral/">terms &amp; conditions</a> of the Referral Program and want to start earning money now.</label></li>
			</ul>
			<p><input type="submit" class="submit" value="Join Now" /></p>
		</form>
		{else}
		<p>Check on <strong><a href="/account/referrals/">your referrals</a></strong> and earn money with Tactile CRM.</p>
		{/if}
	{/foldable}
</div>
<div id="the_page">
	<div class="admin_holder">
		<div id="page_title">
			<h2>Admin Settings</h2>
		</div>
		<div class="content_holder">
			<h3>Users &amp; Groups</h3>
			<ul class="admin_list">
				<li>
					<h4><a class="group" href="/users/">Manage Users &raquo;</a></h4>
					<p>Add additional Users, and control their access to Tactile CRM.</p>
				</li>
				<li>
					<h4><a class="group" href="/groups/">Manage Groups &raquo;</a></h4>
					<p>User Groups can be used to control access permissions.</p>
				</li>
				<li>
					<h4><a class="group" href="/permissions/">User Permissions &raquo;</a></h4>
					<p>Control non-admin access to the features of Tactile CRM<!--, and set default permissions-->.</p>
				</li>
			</ul>
		</div>
		<div class="content_holder">
			<h3>Your Tactile Account</h3>
			<ul class="admin_list">
				<li>
					<h4><a class="group" href="/account/">Account Information &raquo;</a></h4>
					<p>{if $current_plan->is_free()}Upgrade{else}Manage{/if} your Plan, payment details, and usage limits.</p>
				</li>
				{if !$current_plan->is_free()}
				<li>
					<h4><a class="group" href="/account/invoices">Payment Invoices &raquo;</a></h4>
					<p>View your payments history, and request invoice copies.</p>
				</li>
				{/if}
				<li>
					<h4><a class="group" href="/appearance/">Customise Appearance &raquo;</a></h4>
					<p>Change the colour scheme of Tactile CRM to suit your style.</p>
				</li>
			</ul>
		</div>
		<div class="content_holder">
			<h3>Data & Templates</h3>
			<ul class="admin_list">
				<li>
					<h4><a class="group" href="/setup/">Configurable Values &raquo;</a></h4>
					<p>Control the options available for classifying your Organisations, Opportunities, and Activities.</p>
				</li>
				<li>
					<h4><a class="group" href="/customfields/">Custom Fields &raquo;</a></h4>
					<p>Create custom fields and make them available to Organisations, People, Opportunities and Activities.</p>
				</li>
				<li>
					<h4><a class="group" href="/setup/email/">TactileMail &raquo;</a></h4>
					<p>Create templates and manage the email addresses your Users can send from.</p>
				</li>
				{if !$current_plan->is_free() || $current_user->getAccount()->in_trial()}
				<li>
					<h4><a class="group" href="/tracks">Activity Tracks &raquo;</a></h4>
					<p>Create and manage series of Activities that can be added simultaneously.</p>
				</li>
				{/if}
			</ul>
		</div>
		<div class="content_holder">
			<h3>External Applications</h3>
			<ul class="admin_list">
				<li>
					<h4><a class="group" href="/webform/">Configure Tactile CRM Web Form &raquo;</a></h4>
					<p>The Tactile CRM Web Form is a safe and easy way of capturing customer data on your website.</p>
				</li>
				<li>
					<h4><a class="group" href="/api/">Toggle Tactile CRM API access &raquo;</a></h4>
					<p>The Tactile CRM API can be used to allow secure external access to your data.</p>
				</li>
				<li>
					<h4><a class="group" href="/campaignmonitor/">Link Tactile CRM with your Campaign Monitor account &raquo;</a></h4>
					<p>Link with Campaign Monitor to send email campaigns to your Tactile CRM contacts.</p>
				</li>
				<li>
					<h4><a class="group" href="/freshbooks/">Link Tactile CRM with your FreshBooks account &raquo;</a></h4>
					<p>Link with FreshBooks to keep track of your customer invoices within Tactile CRM.</p>
				</li>
				{*<li>
					<h4><a class="group" href="/xero/">Link Tactile CRM with your Xero account &raquo;</a></h4>
					<p>Link with Xero to keep track of your customer invoices within Tactile CRM.</p>
				</li>*}
				<li>
					<h4><a class="group" href="/zendesk/">Link Tactile CRM with your Zendesk account &raquo;</a></h4>
					<p>Link with ZenDesk to show support tickets against your contacts in Tactile CRM.</p>
				</li>
			</ul>
		</div>
	</div>
</div>
