<div id="welcome_message" class="dashboard_item">
	<div id="welcome_text">
		<p id="no_show"><a class="action" href="/magic/hide_welcome_message/" id="hide_welcome_message">Hide this message</a></p>
		<h2>Getting Started with Tactile CRM</h2>
		<ul class="bullets">
			<li><strong><a id="welcome_add_person">Add a Person</a></strong>
			or <strong><a id="welcome_add_organisation">Organisation</a></strong> to Tactile CRM,
			or <strong><a href="/import/">import</a></strong> contacts from <strong><a href="/import/csv">CSV</a></strong>,
			<strong><a href="/import/google">Google Mail</a></strong>,
			or <strong><a href="/import/vcard">vCards</a></strong>.</li>
			<li>Create your own personal <strong><a href="/emails/all">dropbox</a></strong> to help keep on top of your emails.</li>
			<li>Start tracking your business by <strong><a href="/opportunities/new">adding Opportunities</a></strong>.</li>
			{if $current_user->isAdmin()}
			<li>As an administrator you might want to <strong><a href="/users/new/">add another User</a></strong>,
			or <strong><a href="/setup/">customise the configurable fields</a></strong>
			we've set up for you.</li>
			{/if}
		</ul>
		<p>Got a question? Ask us via the <strong><a id="support_link_highlight">Support</a></strong> link at the top of the page.</p>
		<p>Want to find out more? Why not sign up for one of our <strong><a href="http://www.tactilecrm.com/webinar">free webinars</a></strong> and talk with us.</p>
	</div>
</div>
