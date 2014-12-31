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
<div id="the_page">
	<div class="edit_holder">
		{if $current_plan->is_per_user()}
		<div id="page_title">
			<h2>Upgrade from {$current_plan->name} to {$paid_plan->name}</h2>
		</div>
		<form action="{*$smarty.const.SERVER_ROOT|replace:'http://':'https://'*}/account/process_plan_change" method="post" class="saveform" id="per_user_change_plan_form">
			{if $paid_plan->name eq 'Enterprise'}<input type="hidden" name="plan" value="9" />
			{else}<input type="hidden" name="plan" value="11" />{/if}
			<div class="content_holder">
				<fieldset>
				<div class="form_help">
					<p>Upgrading gives you <strong>unlimited opportunities and contacts</strong>, the ability to add additional Users, increased file storage, and priority email support. {if $paid_plan->name eq 'Premium'}If you want <strong>full phone support you change switch to the <a href="/account/change_plan/?plan=enterprise">Enterprise Plan</a>.</strong>{/if}</p>
				</div>
				<h3>User Allowance</h3>
					<div class="content">
						<div class="row">
							<label for="users_quantity">User Limit</label>
							<input type="text" id="user_quantity" name="quantity" value="3" />
						</div>
						<div class="row">
							<span class="false_label">Cost per User per Month</span>
							<span class="false_input">{$currency}{$cpupm|pricify}</span>
						</div>
						<input type="hidden" id="cpupm" value="{$cpupm}" />
						<div class="row">
							<div id="purchase_users_cart">
								<span class="false_label">Total</span>
								<span class="false_input" id="purchase_users_total">{$currency}{$cpupm*3|pricify}</span>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset>
					<h3>Payment Details</h3>
					<div class="content">
						{include file="elements/payment_form_fields.tpl"}
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<div class="content">
					<p><em>Upgrading your Plan will charge your card <strong>today</strong> for the total amount stated above. You will be billed every <strong>30</strong> days thereafter.</em></p>
					<label class="inline_checkbox" id="purchase_users_confirm">Click the following box to confirm you have read the above and understood it: <input type="checkbox" name="confirm" class="checkbox" /></label>
				</div>
			</div>
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Change Plan" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
		{else}
		<div id="page_title">
			<h2>Change Your Plan</h2>
		</div>
		<form action="{$smarty.const.SERVER_ROOT|replace:'http://':'https://'}/account/process_plan_change" method="post" class="saveform">
			<div class="content_holder">
				<table id="plan_comparison">
					<col />
					<col class="{if !$available.free}unavailable{elseif $current_plan->id eq $plans.free}current{/if}" />
					<col class="{if !$available.micro}unavailable{elseif $current_plan->id eq $plans.micro}current{/if}" />
					<col class="{if !$available.sme}unavailable{elseif $current_plan->id eq $plans.sme}current{/if}" />
					<col class="{if !$available.business}unavailable{elseif $current_plan->id eq $plans.business}current{/if}" />
					<col class="{if !$available.premier}unavailable{elseif $current_plan->id eq $plans.premier}current{/if}" />
					<col class="{if !$available.enterprise}unavailable{elseif $current_plan->id eq $plans.enterprise}current{/if}" />
					<thead>
						<tr>
							<td>&nbsp;</td>
							<th>Free</th>
							<th>Micro</th>
							<th>SME</th>
							<th>Business</th>
							<th>Premier</th>
						<tr>
					</thead>
					<tfoot id="change_plan_options">
						<tr>
							<th>&nbsp;</th>
							<td>
								{if $available.free}
									<input type="radio" name="plan" value="{$plans.free}"{if $current_plan->id eq $plans.free} checked="checked" class="current checkbox"{else} class="checkbox"{/if} />
								{else}
									n/a
								{/if}
							</td>
							<td>
								{if $available.micro}
									<input type="radio" name="plan" value="{$plans.micro}"{if $current_plan->id eq $plans.micro} checked="checked" class="current checkbox notfree"{else} class="checkbox notfree"{/if} />
								{else}
									n/a
								{/if}
							</td>
							<td>
								{if $available.sme}
								<input type="radio" name="plan" value="{$plans.sme}" {if $current_plan->id eq $plans.sme} checked="checked"class="current checkbox notfree"{else} class="checkbox notfree"{/if} />
								{else}
									n/a
								{/if}
							</td>
							<td>
								{if $available.business}
								<input type="radio" name="plan" value="{$plans.business}" {if $current_plan->id eq $plans.business} checked="checked" class="current checkbox notfree"{else} class="checkbox notfree"{/if} />
								{else}
									n/a
								{/if}
							</td>
							<td>
								{if $available.premier}
								<input type="radio" name="plan" value="{$plans.premier}" {if $current_plan->id eq $plans.premier} checked="checked" class="current checkbox notfree"{else} class="checkbox notfree"{/if} />
								{else}
									n/a
								{/if}
							</td>
						</tr>																	
					</tfoot>
					<tbody>
						<tr>
							<th scope="row">Cost (per Month)</th>
							<td>Free</td>
							<td>&pound;6</td>
							<td>&pound;15</td>
							<td>&pound;35</td>	
							<td>&pound;60</td>	
						</tr>
						<tr>
							<th scope="row">Users</th>
							<td>2</td>
							<td>3</td>
							<td>7</td>
							<td>20</td>	
							<td>50</td>	
						</tr>
						<tr>
							<th scope="row">Opportunities</th>
							<td>2</td>
							<td>25</td>
							<td>50</td>
							<td>Unlimited</td>
							<td>Unlimited</td>
						</tr>
						<tr>
							<th scope="row">Contacts</th>
							<td>250</td>
							<td>1,000</td>
							<td>7,500</td>
							<td>25,000</td>
							<td>100,000</td>
						</tr>
						<tr>
							<th scope="row">File Space</th>
							<td>10MB</td>
							<td>250MB</td>
							<td>750MB</td>
							<td>5GB</td>	
							<td>10GB</td>	
						</tr>
						<tr>
							<th scope="row">SSL</th>
							<td><img src="/graphics/tactile/true.png" alt="Yes" /></td>
							<td><img src="/graphics/tactile/true.png" alt="Yes" /></td>
							<td><img src="/graphics/tactile/true.png" alt="Yes" /></td>
							<td><img src="/graphics/tactile/true.png" alt="Yes" /></td>
							<td><img src="/graphics/tactile/true.png" alt="Yes" /></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="content_holder" style="display:none" id="change_plan_card_form">
				{if $needs_card_details}
				<div class="form_help">
					<p>Thanks for choosing to upgrade your account. To change plan, we'll need your card details. 
					These are sent to our payment-processor over a secure connection.</p>
					<p>Payment will be taken <strong>today</strong>, and every 30 days thereafter.</p>
				</div>
				<div class="content">
					{include file="elements/payment_form_fields.tpl"}
				</div>
				{else}
				<div class="form_help">
					<p>Thanks for choosing to upgrade your account. By clicking 'Change Plan',
					your account will be upgraded instantly and you will be charged for the new amount on your next billing date.</p>
				</div>
				{/if}
			</div>
			<fieldset id="save_container">
				<input type="submit" value="Change Plan" />
			</fieldset>
		</form>
		{/if}
	</div>
</div>
