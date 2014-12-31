<div id="right_bar">
	{foldable}
		<p><a href="/users/">Back to User List</a></p>
	{/foldable}
	{foldable key="user_usage" title="User Usage"}
		<p>You are currently using <strong>{$users_used}</strong> of your <strong>{$users_limit}</strong> User limit</p>
	{/foldable}
	{foldable extra_class="help" title="User Limit Help" key="purchase_users_help"}
		<p>Your account is billed every 30 days according to your User Limit. If your limit is 5, you will be charged for 5 Users, regardless of how many you are using.</p>
		<p>Your usage only includes <em>enabled</em> Users.</p>
	{/foldable}
	{foldable key="card_types" title="Accepted Cards" extra_class="no-background"}
		<div class="t-center">
			<img src="/graphics/tactile/card_types.png" alt="Visa, MasterCard" />
		</div> 
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Purchase Additional Users</h2>
		</div>
		<form action="/users/process_purchase/" method="post" class="saveform" id="purchase_users_form">
			<div class="content_holder">
				<fieldset>
					<div class="form_help">
						<p>Your next billing date is: <strong>{$billing_date|date_format}</strong>,
						which is <strong>{$days_to_next_bill}</strong> day{if $days_to_next_bill > 1}s{/if} away.</p>
					</div>
					<div class="content">
						<div class="row">
							<label for="user_quantity">Increase Limit By</label>
							<input id="user_quantity" type="text" name="quantity" value="3" />
							<input id="current_limit" type="hidden" value="{$users_limit}" />
						</div>
						<div class="row">
							<span class="false_label">Cost per User per Month</span>
							<span class="false_input">{$currency}{$cpupm|pricify}</span>
						</div>
						<div class="row">
							<span class="false_label">Pro-rata Days</span>
							<span class="false_input">{$pro_rata_days}</span>
						</div>
						<div class="row">
							<span class="false_label">Pro-rata Cost</span>
							<span class="false_input">{$currency}{$pro_rata_cost|pricify}</span>
							<input type="hidden" id="purchase_users_pro_rata_cost" value="{$pro_rata_cost}" />
						</div>
						<div class="row">
							<div id="purchase_users_cart">
								<span class="false_label">Charge Today</span>
								<span class="false_input" id="purchase_users_total">{$currency}{$pro_rata_cost*3|pricify}</span>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<div class="content">
					{include file="elements/payment_form_fields.tpl"}
				</div>
			</div>
			<div class="content_holder">
				<div class="content">
					<p><em>Purchasing new Users will charge your card <strong>today</strong> for the total amount stated above,
					and your next bill will charge you for <strong id="total_users">{math equation="x + 3" x=$users_limit}</strong> Users.</em></p>
					<label class="inline_checkbox" id="purchase_users_confirm">Click the following box to confirm you have read the above and understood it: <input type="checkbox" name="confirm" class="checkbox" /></label>
				</div>
			</div>
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Purchase" id="purchase_users_submit" />	
						</div>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>
