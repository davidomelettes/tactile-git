<script type="text/javascript">
	Tactile.opportunity_statuses = {$opp_statuses|@json_encode};
</script>
<div id="right_bar">
	{foldable}
		<p><a href="/emails/">Back to My Emails</a></p>
	{/foldable}
	{foldable key="dropbox_info" title="Dropbox Information"}
		{if $dropboxkey neq ''}
		<p>To attach an email to one of your contacts, bcc (outgoing) or forward (incoming) it to:</p>
		<p><a href="mailto:{$current_user->getDropboxAddress()}">{$current_user->getDropboxAddress()}</a></p>
		{else}
		<p>You have not yet set a Dropbox key.</p>
		{/if}
	{/foldable}
	{foldable key="email_help" title="Email Help" extra_class="help"}
	<p>You can assign an email to any combination of a Person, Organisation, and Opportunity.</p>
	{/foldable}
	{foldable key="email_add_contacts" title="Add Contacts"}
	<ul class="sidebar_options">
		<li><a id="assign_add_organisation" class="sprite sprite-organisation">Add an Organisation</a></li>
		<li><a id="assign_add_person" class="sprite sprite-person">Add a Person</a></li>
		<li><a id="assign_add_opportunity" class="sprite sprite-opportunity">Add an Opportunity</a></li>
	</ul>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Assign Email</h2>
		</div>
		<div class="content_holder no_assign">
			<ul class="timeline">
				<li class="item">
				{include file="elements/timeline/email.tpl"}
				</li>
			</ul>
		</div>
		<form action="/emails/save/" method="post" class="saveform">
			<div class="content_holder">
				{with model=$email}
				{input type="hidden" attribute="id"}
				<fieldset id="email_info">
					<h3>Assign to...</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>To assign an email, type the name of at least a Person, Organisation, or Opportunity.</p>
					</div>
					<div class="content">
						{input type="hidden" attribute="person_id"}
						{input type="text" attribute="person"}
					</div>
				</fieldset>
			</div>
			{if !$email->person_id && ($email->getDirection() eq 'incoming' || $email->getDirection() eq 'outgoing')}
			<div class="content_holder">
				<fieldset>
					<div class="content">
						<label class="inline_checkbox">
							Add '{if $email->getDirection() eq 'incoming'}{$email->email_from}{elseif $email->getDirection() eq 'outgoing'}{$email->email_to}{/if}' to attached Person? 
							<input type="checkbox" class="checkbox" name="email_assign" id="email_assign" />
						</label>
					</div>
				</fieldset>
			</div>
			{/if}
			<div class="content_holder">
				<fieldset>
					<div class="content">
						{input type="hidden" attribute="organisation_id"}
						{input type="text" attribute="organisation" label="Organisation"}
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset>
					<div class="content">
						{input type="hidden" attribute="opportunity_id"}
						{input type="text" attribute="opportunity"}
						{/with}
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Save" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>