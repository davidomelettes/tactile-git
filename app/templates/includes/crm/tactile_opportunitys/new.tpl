{if !$partial}
<div id="right_bar">
	{*foldable key="opp_new_help" title="Opportunity Help" extra_class="help"}
		<ul class="help_options">
			<li><a>Further help with this form</a></li>
			<li><a>Video tutorial</a></li>
		</ul>
	{/foldable*}
</div>
{/if}
<div id="{if $partial}partial_page{else}the_page{/if}">
	<div class="edit_holder" id="new_opportunity">
		<div id="page_title">
			{if $Opportunity->id}
			<h2>Editing {$Opportunity->getFormatted('name')}</h2>
			{else}
			<h2>New Opportunity</h2>
			{/if}
		</div>
		<form action="/opportunities/save/" method="post" class="saveform">
			{with model=$Opportunity}
			<div class="content_holder">
				<fieldset id="opportunity_basic_info">
					<h3>Opportunity Details</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>Name and Expected Close Date are required fields.</p>
						<p>The Expected Close Date is used in Tactile's reporting to calculate your sales pipeline, etc.</p>
					</div>
					<div class="content">
						{input type="hidden" attribute="id"}
						{input type="text" attribute="name" label="Summary"}
						{input type="hidden" attribute="organisation_id"}
						{input type="text" attribute="organisation" label="Organisation"}
						{input type="hidden" attribute="person_id"}
						{input type="text" attribute="person"}
						{input type="date" attribute="enddate" label="Expected Close Date"}
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<p>You can <a href="#opportunity_pipeline_details" class="show_fields highlight">enter pipeline details</a>.</p>
				<fieldset id="opportunity_pipeline_details" style="display:none;">
					<h3>Pipeline Details</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>An Opportunity's Value and Probability control its weight in Tactile's reports.</p>
						<p>{if $current_user->isAdmin()}
						You can change the available Sales Stage options from the <a href="/admin/">Admin</a> page.
						{else}
						Your account admin can customise the available Sales Stage options from the Admin page.
						{/if}</p>
					</div>
					<div class="content">
						{input  type="text" attribute="cost" label="Value"}
						{select attribute="status_id" label="Sales Stage"}
						{select attribute="probability"}
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<p>Or specify <a href="#opportunities_sales_details" class="show_fields highlight">sales details</a></p>
				<fieldset id="opportunities_sales_details"  style="display:none;">
					<h3>Sales Details</h3>
					<div class="form_help">
						<p>{if $current_user->isAdmin()}
						You can change the available Type and Source options from the <a href="/admin/">Admin</a> page.
						{else}
						Your account admin can customise the available Type and Source options from the Admin page.
						{/if}</p>
					</div>
					<div class="content">
						{select attribute="type_id"}
						{select attribute="source_id"}
						{select attribute="assigned_to" label="Assigned To"}
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<fieldset id="opportunities_description">
					<h3>Description</h3>
					<div class="content">
						<div class="row">
							<textarea id="Opportunity_description" name="Opportunity[description]">{$smarty.session._controller_data.Opportunity.description|stripslashes|default:$Opportunity->description}</textarea>
						</div>
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
