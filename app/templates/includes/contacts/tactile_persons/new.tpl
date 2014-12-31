<div id="right_bar">
	{foldable key="person_options" title="Alternatives"}
		<ul class="sidebar_options">
			<li><a href="/import/" class="sprite sprite-import">Import Contacts into Tactile</a></li>
			<li><a href="/organisations/new/" class="sprite sprite-organisation">Add an Organisation</a></li>
		</ul>
	{/foldable}
	{*foldable key="person_new_help" title="Person Help" extra_class="help"}
		<ul class="help_options">
			<li><a>Further help with this form</a></li>
			<li><a>Video tutorial</a></li>
		</ul>
	{/foldable*}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			{if $Person->id}
			<h2>Editing {$Person->getFormatted('firstname')} {$Person->getFormatted('surname')}</h2>
			{else}
			<h2>New Person</h2>
			{/if}
		</div>
		<form action="/people/save/" method="post" class="saveform">
			{with model=$Person}
			<div class="content_holder">
				<fieldset id="person_basic_info">
					<h3>Basic Info</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>Firstname and Surname are required fields.</p>
						<p>People will inherit the access permissions of any Organisation they are attached to.</p>
					</div>
					<div class="content">
						{input type="hidden" attribute="id"}
						{input type="text" attribute="title"}
						{input type="text" attribute="firstname"}
						{input type="text" attribute="surname"}
						{input type="text" attribute="suffix"}
						{input  type="text" attribute="jobtitle"}
						{input type="hidden" attribute="organisation_id"}
					</div>
				</fieldset>
			</div>
			{if !$Person->id || !$Person->isUser()}
			<div class="content_holder">
				<fieldset>
					<div class="content">
						{input type="text" attribute="organisation" label="Organisation"}
					</div>
				</fieldset>
			</div>
			{/if}
			
			<div class="content_holder">
				<p style="{if $Person->hasAddress()}display: none;{/if}"><a href="#client_address" class="show_fields highlight">Click here to add an address</a> for this Person.</p>
				<fieldset id="client_address" style="{if !$Person->hasAddress()}display: none;{/if}">
					<h3>Address</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>Additional addresses can be added {if $Person->id}via the Addresses panel{else}once this Person has been created{/if}.</p>
					</div>
					<div class="content">
						{with alias='address'}
							{input type="hidden" attribute="id"}
							{input type="text" attribute="street1" label="Street 1"}
							{input type="text" attribute="street2" label="Street 2"}
							{input type="text" attribute="street3" label="Street 3"}
							{input type="text" attribute="town" label="Town / City"}
							{input type="text" attribute="county" label="County / State"}
							{input type="text" attribute="postcode" label="Postcode / ZIP"}
							{select type="text" attribute="country_code" label="Country"}
						{/with}
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<fieldset id="client_contact_details">
					<h3>Contact Details</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>Additional contact details can be added once this Person has been created.</p>
					</div>
					<div class="content">
					{with alias='phone'}
						{input type="text" attribute="contact" label="Phone"}
						{input type="hidden" attribute="id"}
					{/with}
					{with alias='mobile'}
						{input type="text" attribute="contact" label="Mobile"}
						{input type="hidden" attribute="id"}
					{/with}
					{with alias='email'}
						{input type="text" attribute="contact" label="Email"}
						{input type="hidden" attribute="id"}
					{/with}
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<fieldset id="person_permissions">
					<h3>Access Permissions</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>Mark a Person as 'private' to allow only the owner to see it and make changes.</p>
						<p>If this Person is added to an Organisation, the inherited permissions will override this option.</p>
					</div>
					<div class="content">
						{if $Person->organisation_id}
						{input type="checkbox" attribute="private" label="Private" disabled=true}
						{else}
						{input type="checkbox" attribute="private" label="Private?"}
						{/if}
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">				
				<fieldset id="person_description">
					<h3>Description</h3>
					<div class="content">
						<div class="row">
							<textarea id="Person_description" name="Person[description]" rows="4" cols="20">{$Person->description}</textarea>
						</div>
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<p>Click to add <a href="#person_extra_info" class="show_fields highlight">Extra Info</a></p>
				<fieldset id="person_extra_info" style="display:none;">
					<h3>Extra Info</h3>
					<div class="form_help">
						<p>The 'Person Reports To' field can be used to establish the hierarchy of an Organisation.</p>
					</div>
					<div class="content">
						{input type="date" attribute="dob" label="Date of Birth"}
						{input type="hidden" attribute="reports_to"}
						{input type="text" attribute="person_reports_to"}
						{input type="checkbox" attribute="can_call"}
						{input type="checkbox" attribute="can_email"}
						{select attribute="language_code" label="Language"}
						{select attribute="assigned_to"}
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
			{/with}
		</form>
	</div>
</div>
