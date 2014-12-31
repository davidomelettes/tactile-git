{if $webform_enabled}
<form action="http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}" method="post" id="tactile_webform">
	<div id="tactile_form_flash">{flash}{$flash}</div>
	<fieldset>
		{if $capture_person}
		<p id="tactile_webform_capture_person_firstname">
			<label for="tactile_form_firstname">{$form.firstname|escape}{if $capture_person == 'required'}*{/if}</label>
			<input class="text" id="tactile_form_firstname" type="text" name="Person[firstname]" value="{$data.Person.firstname}" />
		</p>
		<p id="tactile_webform_capture_person_surname">
			<label for="tactile_form_surname">{$form.surname|escape}{if $capture_person == 'required'}*{/if}</label>
			<input class="text" id="tactile_form_surname" type="text" name="Person[surname]" value="{$data.Person.surname}" />
		</p>
		{/if}
		{if $capture_organisation}
		<p id="tactile_webform_capture_organisation">
			<label for="tactile_form_org">{$form.organisation|escape}{if $capture_organisation == 'required'}*{/if}</label>
			<input class="text" id="tactile_form_org" type="text" name="Organisation[name]" value="{$data.Organisation.name}" />
		</p>
		{/if}
		{if $capture_contact}
		<p id="tactile_webform_capture_contact_phone">
			<label for="tactile_form_phone">{$form.phone|escape}{if $capture_contact == 'required'}*{/if}</label>
			<input class="text" id="tactile_form_phone" type="text" name="phone[contact]" value="{$data.phone.contact}" />
		</p>
		<p id="tactile_webform_capture_contact_email">
			<label for="tactile_form_email">{$form.email|escape}{if $capture_contact == 'required'}*{/if}</label>
			<input class="text" id="tactile_form_email" type="text" name="email[contact]" value="{$data.email.contact}" />
		</p>
		{/if}
	</fieldset>
	{if $create_opportunity}
	<fieldset>
		{if $has_options}
		<p id="tactile_webform_capture_opportunity_options">
			<label for="tactile_form_option">{$form.options_label|escape}</label>
			<select id="tactile_form_option" name="option">
				{foreach from=$form.options item=option}
				<option value="{$option|escape}"{if $data.option == $option} selected="selected"{/if}>{$option|escape}</option>
				{/foreach}
			</select>
		</p>
		{/if}
	</fieldset>
	{/if}
	{if $webform_use_captcha}
	<div id="recaptcha_widget" style="margin-top: 10px; display: none;">
		<div id="recaptcha_image"></div>
		<fieldset>
			<div id="recaptcha_buttons">
				<img id="recaptcha_audio" src="/graphics/tactile/icons/sound.png" onclick="Recaptcha.switch_type('audio');" alt="Click to hear an audio version" title="Click to hear an audio version" />
				<img id="recaptcha_refresh" src="/graphics/tactile/icons/refresh.png" onclick="Recaptcha.reload();" alt="Click to reload image" title="Click to reload image" />
			</div>
			<p id="recaptcha_label">
				<label for="recaptcha_response_field">{$form.captcha_prompt|escape}*</label>
			</p>
			<p style="padding-left: 8px;">
				<input class="text" type="text" id="recaptcha_response_field" name="recaptcha_response_field" style="width: 100%;" />
			</p>
		</fieldset>
		<script type="text/javascript">var RecaptchaOptions = {literal}{"theme":"custom","custom_theme_widget":"recaptcha_widget"}{/literal}</script>
		<script type="text/javascript" src="http://api.recaptcha.net/challenge?k={$recaptcha_public_key}"></script>
		<noscript>
			<iframe src="http://api.recaptcha.net/noscript?k={$recaptcha_public_key}"
				height="300" width="500" frameborder="0"></iframe><br />
			<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
			<input type="hidden" name="recaptcha_response_field" value="manual_challenge" />
		</noscript>
	</div>
	{/if}
	<fieldset>
		<p id="tactile_webform_query_label"><label for="tactile_form_query">{$form.query|escape}*</label></p>
		<p id="tactile_webform_query">
			<textarea rows="5" cols="28" id="tactile_form_query" name="query">{$data.query}</textarea>
		</p>
	</fieldset>
	<fieldset id="tactile_webform_submit_fieldset">
		<p id="tactile_webform_required">* Required Fields</p>
		<p id="tactile_webform_submit">
			{if $current_user->getAccount()->is_free()}<span id="tactile_webform_callout">Powered by <a href="http://www.tactilecrm.com">Tactile CRM</a></span>{/if}
			<input class="submit" type="submit" value="Submit" />
		</p>
		{assign var=account value=$current_user->getAccount()}
		<input type="hidden" name="id" value="{$account->id}" />
	</fieldset>
</form>
{/if}