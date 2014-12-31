<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable key="webform_help" title="Web Form Help" extra_class="help"}
		<p>Use the options on the left to control which fields are present in your form, and how they are labeled.</p>
	{/foldable}
	{if $webform_enabled}
	<iframe src="/form_html/" width="100%" height="650"></iframe>
	{/if}
</div>
<div id="the_page">
	<div class="admin_holder">
		<div id="page_title">
			<h2>Tactile CRM Web Form</h2>
		</div>
		<form action="/webform/save/" method="post" class="saveform" id="edit_tactile_webform">
			<div class="content_holder">
				<fieldset>
					<h3>Toggle Web Form Access</h3>
					<div class="form_help">
						<p>To use the webform, copy and paste the following HTML into your site:</p>
						<pre>&lt;iframe src="http://{$smarty.server.HTTP_HOST}/form_html/" width="300" height="650"&gt;&lt;/iframe&gt;</pre>
					</div>
					<div class="content">
						<div class="row">
							<label for="webform_enabled">Enable Web Form?</label>
							<input class="checkbox" type="checkbox" id="webform_enabled" name="webform_enabled"{if $webform_enabled} checked="checked"{/if} />
						</div>
						<div class="row">
							<label for="webform_owner">Work As User</label>
							<select id="webform_owner" name="webform_owner">
								{foreach from=$users item=user key=key}
								<option value="{$key}"{if $form.owner == $key}selected="selected"{/if}>{$user}</option>
								{/foreach}
							</select>
						</div>
						<div class="row">
							<label for="webform_email_to">Email Notification To</label>
							<input type="text" id="webform_email_to" name="webform_email_to" value="{$form.email_to}" />
						</div>
						<div class="row">
							<label for="webform_success_msg">Success Message</label>
							<input type="text" id="webform_success_msg" name="webform_success_msg" value="{$form.success_msg}" />
						</div>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset>
					<h3>Form Capture Details</h3>
					<div class="content">
						<div class="row">
							<label for="webform_use_captcha">Use Anti-Spam?</label>
							<input class="checkbox" type="checkbox" id="webform_use_captcha" name="webform_use_captcha"{if $webform_use_captcha} checked="checked"{/if} />
						</div>
						<div id="captcha_fields"{if !$webform_use_captcha} style="display: none;"{/if}>
							<div class="row">
								<label for="edit_tactile_form_captcha_prompt">Anti-Spam Prompt</label>
								<input id="edit_tactile_form_captcha_prompt" type="text" name="webform_captcha_prompt" value="{$form.captcha_prompt}" />
							</div>
						</div>
						<div class="row">
							<label for="edit_tactile_form_query">Your Message Label</label>
							<input id="edit_tactile_form_query" type="text" name="query" value="{$form.query}" />
						</div>
						<div class="row">						
							<label>Capture Person?</label>
							<div class="false_input radio_options">
								<label>
									<input class="radio checkbox capture_person" type="radio" id="capture_person_no" value="" name="capture_person"{if !$capture_person} checked="checked"{/if} />
									No
								</label>
								<label>
									<input class="radio checkbox capture_person" type="radio" id="capture_person_optional" value="optional" name="capture_person"{if $capture_person eq 'optional'} checked="checked"{/if} />
									Yes
								</label>
								<label>
									<input class="radio checkbox capture_person" type="radio" id="capture_person_required" value="required" name="capture_person"{if $capture_person eq 'required'} checked="checked"{/if} />
									Required
								</label>
							</div>
						</div>
						<div id="capture_person_fields"{if !$capture_person} style="display: none;"{/if}>
							<div class="row">
								<label for="edit_tactile_form_firstname">First Name Label</label>
								<input id="edit_tactile_form_firstname" type="text" name="firstname" value="{$form.firstname}" />
							</div>
							<div class="row">
								<label for="edit_tactile_form_surname">Last Name Label</label>
								<input id="edit_tactile_form_surname" type="text" name="surname" value="{$form.surname}" />
							</div>
						</div>
						
						<div class="row">
							<label for="capture_organisation">Capture Organisation?</label>
							<div class="false_input radio_options">
								<label>
									<input class="radio checkbox capture_organisation" type="radio" id="capture_organisation_no" value="" name="capture_organisation"{if !$capture_organisation} checked="checked"{/if} />
									No
								</label>
								<label>
									<input class="radio checkbox capture_organisation" type="radio" id="capture_organisation_optional" value="optional" name="capture_organisation"{if $capture_organisation eq 'optional'} checked="checked"{/if} />
									Yes
								</label>
								<label>
									<input class="radio checkbox capture_organisation" type="radio" id="capture_organisation_required" value="required" name="capture_organisation"{if $capture_organisation eq 'required'} checked="checked"{/if} />
									Required
								</label>
							</div>
						</div>
						<div id="capture_organisation_fields"{if !$capture_organisation} style="display: none;"{/if}>
							<div class="row">
								<label for="edit_tactile_form_org">Organisation Label</label>
								<input id="edit_tactile_form_org" type="text" name="organisation" value="{$form.organisation}" />
							</div>
						</div>
						
						<div class="row">
							<label for="capture_contact">Capture Contact Details?</label>
							<div class="false_input radio_options">
								<label>
									<input class="radio checkbox capture_contact" type="radio" id="capture_contact_no" value="" name="capture_contact"{if !$capture_contact} checked="checked"{/if} />
									No
								</label>
								<label>
									<input class="radio checkbox capture_contact" type="radio" id="capture_contact_optional" value="optional" name="capture_contact"{if $capture_contact eq 'optional'} checked="checked"{/if} />
									Yes
								</label>
								<label>
									<input class="radio checkbox capture_contact" type="radio" id="capture_contact_required" value="required" name="capture_contact"{if $capture_contact eq 'required'} checked="checked"{/if} />
									Required
								</label>
							</div>
						</div>
						<div id="capture_contact_fields"{if !$capture_contact} style="display: none;"{/if}>
							<div class="row">
								<label for="edit_tactile_form_phone">Phone Number Label</label>
								<input id="edit_tactile_form_phone" type="text" name="phone" value="{$form.phone}" />
							</div>
							<div class="row">
								<label for="edit_tactile_form_email">Email Address Label</label>
								<input id="edit_tactile_form_email" type="text" name="email" value="{$form.email}" />
							</div>
						</div>
					</div>
				</fieldset>
				<fieldset>
					<div class="content">
						<div class="row">
							<label for="create_opportunity">Create Opportunity?</label>
							<input class="checkbox" type="checkbox" id="create_opportunity" name="create_opportunity"{if $create_opportunity} checked="checked"{/if} />
						</div>
						<div id="create_opportunity_fields"{if !$create_opportunity} style="display: none;"{/if}>
							<div class="row">
								<label for="edit_tactile_form_options_label">Options Label</label>
								<input id="edit_tactile_form_options_label" type="text" name="options_label" value="{$form.options_label}" />
							</div>
							<div class="row">
								<label for="edit_tactile_form_options">Options</label>
								<input id="edit_tactile_form_options" type="text" name="options" value="{$form.options}" />
							</div>
							<div class="row">
								<label for="edit_tactile_form_enddate">End Date (in X days)</label>
								<input id="edit_tactile_form_enddate" type="text" name="enddate" value="{$form.enddate}" />
							</div>
						</div>
					</div>
				</fieldset>
				<fieldset>
					<div class="content">
						<div class="row">
							<label for="create_activity">Create Activity?</label>
							<input class="checkbox" type="checkbox" id="create_activity" name="create_activity"{if $create_activity} checked="checked"{/if} />
						</div>
						<div id="create_activity_fields"{if !$create_activity} style="display: none;"{/if}>
							<div class="row">
								<label for="edit_tactile_form_assign">Assign Activity To</label>
								<select id="edit_tactile_form_assign" name="assign">
									{foreach from=$users item=user key=key}
									<option value="{$key}"{if $form.assign == $key}selected="selected"{/if}>{$user}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
				</fieldset>
				<fieldset id="save_container" class="prefs_save">
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
