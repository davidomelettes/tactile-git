<div id="right_bar">
	{foldable key="organisation_options" title="Alternatives"}
		<ul class="sidebar_options">
			<li><a href="/import/" class="sprite sprite-import">Import Contacts into Tactile</a></li>
			<li><a href="/people/new/" class="sprite sprite-person">Add a Person</a></li>
		</ul>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			{if $Organisation->id}
			<h2>Editing {$Organisation->getFormatted('name')}</h2>
			{else}
			<h2>New Organisation</h2>
			{/if}
		</div>
		<form action="/organisations/save/" method="post" class="saveform">
			{with model=$Organisation}
			<div class="content_holder">
				<fieldset id="client_basic_info">
					<h3>Basic Info</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>Name is the only required field.</p>
						<p>The Account Number will be automatically generated if left blank.</p>
					</div>
					<div class="content">
						{input type="hidden" attribute="id"}
						{input type="text" attribute="name"} 
						{input  type="text" attribute="accountnumber"}
						{input type="text" attribute="parent" label="Parent Organisation"}
						{input type="hidden" attribute="parent_id"}
						{select attribute="assigned_to" label="Assigned To"}
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<p style="{if $Organisation->hasAddress()}display: none;{/if}"><a href="#client_address" class="show_fields highlight">Click here to add an address</a> for the Organisation.</p>
				<fieldset id="client_address" style="{if !$Organisation->hasAddress()}display: none;{/if}">
					<h3>Address</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>Additional addresses can be added {if $Organisation->id}via the Addresses panel{else}once the Organisation has been created{/if}.</p>
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
						<p>Additional contact details can be added {if $Organisation->id}via the Contact Methods panel{else}once the Organisation has been created{/if}.</p>
					</div>
					<div class="content">
						{with alias='phone'}
							{input type="text" attribute="contact" label="Phone"}
							{input type="hidden" attribute="id"}
						{/with}
						{with alias='fax'}
							{input type="text" attribute="contact" label="Fax"}
							{input type="hidden" attribute="id"}
						{/with}
						{with alias='email'}
							{input type="text" attribute="contact" label="Email"}
							{input type="hidden" attribute="id"}
						{/with}
						{with alias='website'}
							{input type="text" attribute="contact" label="Website"}
							{input type="hidden" attribute="id"}
						{/with}
					</div>
				</fieldset>
			</div>
			{/with}
			
			<div class="content_holder">
				{include file="elements/organisation_sharing.tpl" type="Organisation"}
			</div>
			
			<div class="content_holder">
				<fieldset id="client_description">
					<h3>Description</h3>
					<div class="content">
						<div class="row">
							<textarea id="Organisation_description" name="Organisation[description]" cols="20" rows="4">{$Organisation->description}</textarea>
						</div>
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<p><a href="#client_sales_info" class="show_fields highlight">Click here to fill out Sales Info</a> for the Organisation.</p>
				<fieldset id="client_sales_info" style="display:none;">
					<h3>Sales Info</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>Categorise this Organisation using the fields below.</p>
						<p>{if $current_user->isAdmin()}
						You can change the available options from the <a href="/setup/">Admin</a> page.
						{else}
						Your account admin can customise the available options from the Admin page.
						{/if}</p>
					</div>
					<div class="content">
						{with model=$Organisation}
							{input type="hidden" attribute="id"}
							{select attribute="status_id"}
							{select attribute="source_id"}
							{select attribute="classification_id"}
							{select attribute="rating_id"}
							{select attribute="industry_id"}
							{select attribute="type_id"}
						{/with}
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Save Organisation" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>
