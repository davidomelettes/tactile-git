<div id="the_page" class="suspension_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Enter New Payment Details</h2>
		</div>
		<form action="/suspension/process_payment" method="post" class="saveform">
			<div class="content_holder">
				<fieldset>
					<div class="form_help">
						<p>Two attempts to take payment from your account failed,
						and so until we are provided with new, valid details you're not going to be able to access your account.</p>
					</div>
					<div class="content">
						<div class="row">
							<label for="card_name">Cardholder's Name</label>
							<input class="required" id="card_name" name="Card[cardholder_name]" type="text" value="{$previous.Card.cardholder_name}"/>
						</div>
						<div class="row">
							<label for="country">Country</label>
							<input class="required" id="country" name="Card[country]" type="text" value="{$previous.Card.country}"/>
						</div>
						<div class="row">
							<label for="phone">Phone Number (optional)</label>
							<input id="phone" name="Card[phone]" type="text" value="{$previous.Card.phone}"/>
						</div>
						<div class="row">
							<label for="card_type">Card type</label>
							<select id="card_type" name="Card[card_type]">
								<option value="Visa" {if $previous.Card.card_type eq 'Visa'}selected="selected"{/if}>Visa</option>
								<option value="Master Card" {if $previous.Card.card_type eq 'Master Card'}selected="selected"{/if}>Mastercard</option>
								<option value="Switch" {if $previous.Card.card_type eq 'Switch'}selected="selected"{/if}>Maestro</option>
							</select>
						</div>
						<div class="row">
							<label for="card_number">Card number</label>
							<input type="text" {*autocomplete="off"*} id="card_number" name="Card[card_number]" class="required"/>
						</div>
						<div class="row">
							<label for="card_cv2">CV2</label>
							<input type="text" {*autocomplete="off"*} id="card_cv2" name="Card[cv2]" class="required"/>
						</div>
						<div class="row">
							<label for="card_expiration_month">Expiry Date</label>
							{assign var=year value=$smarty.now|date_format:"%Y"}
							<select id="card_expiration_year" name="Card[card_expiration_year]">
								{section name=years loop=$year+7 start=$year}
								<option value="{$smarty.section.years.index|substr:-2}" {if $previous.Card.card_expiration_year eq $smarty.section.years.index|substr:-2}selected="selected"{/if}>
									{$smarty.section.years.index}
								</option>
								{/section}
							</select>
							<select id="card_expiration_month" name="Card[card_expiration_month]">
								{section name=months loop=12}
								<option value="{$smarty.section.months.index_next|string_format:"%02d"}" {if $previous.Card.card_expiration_month eq $smarty.section.months.index_next|string_format:"%02d"}selected="selected"{/if}>
									{if $smarty.section.months.index_next<10}&nbsp;&nbsp;{/if}{$smarty.section.months.index_next} - {$smarty.section.months.index_next|to_month}
								</option>
								{/section}
							</select>
						</div>
					</div>
				</fieldset>
			</div>
			<fieldset id="save_container">
				<div class="content">
					<div class="row">
						<input type="submit" value="Submit" />
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>