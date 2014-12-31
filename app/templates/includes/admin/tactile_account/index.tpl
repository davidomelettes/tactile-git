<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
</div>
<div id="the_page">
	<div class="admin_holder">
		<div id="page_title">
			<h2>Account Information</h2>
		</div>
		<div class="content_holder">
			<h3>Account Limits</h3>
			<ul class="admin_list">
				<li>
					<h4><a class="group" href="/account/usage/">View Usage &amp; Limits &raquo;</a></h4>
					<p>Keep on top of how you're using Tactile CRM, and whether you are approaching any of the limits for your Account.</p>
				</li>
			</ul>
		</div>
		<div class="content_holder">
			<h3>Account {if $plan->is_per_user() || $plan->is_free()}Details{else}Plan{/if} &amp; Payment</h3>
			<ul class="admin_list">
				<li>
					<h4><a class="group" href="/account/account_details/">Change Account Details &raquo;</a></h4>
					<p>This is the information we use to invoice you and send you newsletters.</p>
				</li>
				{if !$plan->is_free()}
				<li>
					<h4><a class="group" href="/account/payment_details/">Change Payment Details &raquo;</a></h4>
					<p>Update your card details to make sure you never miss a payment.</p>
				</li>
				{/if}
				{if !$plan->is_per_user() || $plan->is_free()}
				<li>
					<h4><a class="group" href="/account/change_plan/">Change Plan &raquo;</a></h4>
					{if $plan->is_per_user()}
					<p>Ready to upgrade to Premium? Do so here.</p>
					{else}
					<p>Want to get more out of Tactile CRM? Change your payment plan to best suit your needs.</p>
					{/if}
				</li>
				{/if}
				<li>
					<h4><a class="group" href="/account/cancel/">Cancel Your Subscription &raquo;</a></h4>
					<p>Tactile CRM not for you? Cancel your account with us here.</p>
				</li>
				<li>
					<h4><a class="group" href="/account/change_owner/">Change Account Owner &raquo;</a></h4>
					<p>Assign another Admin User to be the Account Owner.</p>
				</li>
			</ul>
		</div>
	</div>
</div>
