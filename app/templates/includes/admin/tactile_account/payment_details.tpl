<div id="right_bar">
	{foldable}
		<p><a href="/account/">Back to Account Options</a></p>
	{/foldable}
	{foldable key="card_types" title="Accepted Cards" extra_class="no-background"}
		<div class="t-center">
			<img src="/graphics/tactile/card_types.gif" alt="Visa, MasterCard, Visa Electron, Maestro, Delta, Solo" />
		</div> 
	{/foldable}
</div>
<div id="the_page" class="account_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Enter New Payment Details</h2>
		</div>
		<form action="{$smarty.const.SERVER_ROOT|replace:'http://':'https://'}/account/process_details_change" method="post" class="saveform">
			<div class="content_holder">
				<fieldset>
					<div class="form_help">
						<p><em>Any details you enter here will be used in place of any existing ones.</em></p>
						<p>Clicking 'Submit' will carry out what is known as a 'Deferred Payment' against your card. 
						No money is taken, but a check is carried out with the card issuer that the card is valid,
						and that the requested funds are available. When it is next time to charge you for your subscription, 
						this deferred payment will be 'released', taking the money from your account.
						Your account is set to expire on <em class="with_colour">{$account_expires}</em>, and we will attempt to take payment on or shortly before this date.
						</p>
					</div>
					<div class="content">
						{include file="elements/payment_form_fields.tpl"}
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