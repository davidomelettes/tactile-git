<div id="right_bar">
	{foldable key="personalise" title="Quick Start"}
		<p>Tactile CRM is all about contacts. So a good place to start is to:</p>
		<ul class="sidebar_options">
			<li><a id="welcome_add_organisation" class="sprite sprite-organisation">Add an Organisation</a></li>
			<li><a id="welcome_add_person" class="sprite sprite-person">Add a Person</a></li>
			<li><a href="/import/" class="sprite sprite-import">Import exisiting Contacts</a></li>
		</ul>
	{/foldable}
	{if $current_user->isAdmin()}
	{foldable key="join_webinar" title="Join us for a Webinar" extra_class="usage_warning unfoldable"}
		<p>Why not join us for a <a href="http://www.tactilecrm.com/webinar/?organisation={$user_company_name|escape}">free webinar</a> where you can speak with a member of the team and ask any questions you may have.</p>
	{/foldable}
	{/if}
</div>

<div id="the_page">
	<div id="page_title">
		<div class="paging"><a class="action" href="/magic/hide_welcome_message/" id="hide_welcome_message">Hide this welcome page</a></div>
		<h2>Hi{if $current_user->getPersonFirstName()|trim neq ''} {$current_user->getPersonFirstName()}{/if}, Welcome to Tactile CRM</h2>
		</div>
		<div id="welcome_message" class="dashboard_item">
			{if $current_user->getAccount()->account_age_days() < 3}
			<p>Thank you for taking the time to try out Tactile CRM, we hope you find it a useful tool to help organise your contacts and sales. We've included some (hopefully) useful pointers to get you started and would love to hear your comments, thoughts and feedback about them (and anything else) - just get in touch using the <strong><a id="support_link_highlight">handy support link</a></strong>.</p>
			{else}
			<p>We hope you are finding Tactile CRM useful, we've included some (hopefully) useful pointers to get you started and would love to hear your comments, thoughts and feedback - just get in touch using the <strong><a id="support_link_highlight">handy support link</a></strong>.</p>
			{/if} 
			<h3>Helping you keep in touch and win more sales</h3>
			<p>Check out some of the ways Tactile CRM can help you keep in touch with key contacts, stay organised and win more sales.</p>
			<div id="welcome_points">
				<div id="welcome_contacts">
					<h4>Shared<br />Contacts</h4>
					<img src="/graphics/tactile/welcome/contacts.png">
					<p>Share and access contacts with your team.</p>
				</div>
				<div id="welcome_email">
					<h4>Easy Email Integration</h4>
					<img src="/graphics/tactile/welcome/email.png">
					<p>Include important emails by using <a href="/emails/all">your drop box</a>.</p>
				</div>
				<div id="welcome_graphs">
					<h4>Pipelines &amp; Reports</h4>
					<img src="/graphics/tactile/welcome/graphs.png">
					<p>Pipeline <a href="/graphs/pipeline_report">reports</a> and <a href="/graphs/sales_history">graphs</a> at your finger tips.</p>
				</div>
				<div style="clear: both;"></div>
			</div>
			<div id="welcome_text">
				<h4>Setup Your Account</h4>
				<ul class="bullets">
					<li>Choose your <a href="/preferences/date_time/">timezone and date preferences</a></li>
					<li><a href="/people/view/{$current_user->person_id}">Update your details and photo</a> that other users see</li>
				</ul>
				{if $current_user->isAdmin()}
				<ul class="bullets">
					<li><a href="/appearance/">Choose your own colours</a></li>
					<li><a href="/users/new/">Create a login</a> for other people</li>
				</ul>
				{/if}
			</div>
		</div>
</div>