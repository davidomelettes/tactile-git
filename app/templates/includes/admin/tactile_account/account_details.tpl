<div id="right_bar">
	{foldable}
		<p><a href="/account/">Back to Account Options</a></p>
	{/foldable}
</div>
<div id="the_page" class="account_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Enter New Account Details</h2>
		</div>
		<form action="{$smarty.const.SERVER_ROOT|replace:'http://':'https://'}/account/process_account_details_change" method="post" class="saveform">
			<div class="content_holder">
				<fieldset>
					<div class="form_help">
						<p>These are the details for the account owner. They will be used to send invoices, general account information and newsletters.</p>
					</div>
					<div class="content">
						<fieldset class="account_details">
							<div class="row">
								<label for="company">Company</label>
								<input class="required" id="company" name="Details[company]" type="text" value="{$current.company}"/>
							</div>
							<div class="row">
								<label for="country">Country</label>
								<select class="required" id="country" name="Details[country_code]">
									{html_options options=$country_list selected=$current.country_code|default:$default_country_code}
								</select>
							</div>
							<div class="row">
								<span id="vat_blind">
								<label for="vat_number">VAT Number (optional)</label>
								<input id="vat_number" name="Details[vat_number]" type="text" value="{$current.vat_number}"/>
								</span>
							</div>
							<div class="row">
								<label for="firstname">First name</label>
								<input class="required" id="firstname" name="Details[firstname]" type="text" value="{$current.firstname}"/>
							</div>
							<div class="row">
								<label for="surname">Surname</label>
								<input class="required" id="surname" name="Details[surname]" type="text" value="{$current.surname}"/>
							</div>
							<div class="row">
								<label for="email">Email</label>
								<input class="required" id="email" name="Details[email]" type="text" value="{$current.email}"/>
							</div>
						</fieldset>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Submit" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>