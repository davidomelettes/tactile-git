<div id="right_bar">
	{foldable key="referrals_help" title="Referrals Help" extra_class="help"}
		<p>Earn 20% from all paid signups per month. All you need to do is refer people to use <a href="http://www.tactilecrm.com/referral/{$site_address}">Tactile CRM</a> at <strong><a href="http://www.tactilecrm.com/referral/{$site_address}">your personal referral page</a></strong> and we'll track their signup and purchases.</p>
		<p><strong>We'll pay direct to your account whenever your balance is over &pound;50 at the end of the month.</strong></p>
	{/foldable}
	{if $total_referrals > 0}
		{foldable}
			<p>Thanks for your support of Tactile CRM - so far <strong>you have referred {$total_referrals} sign ups</strong>{if $paid_referrals > 0} and <strong>{$paid_referrals} of them have paid</strong> at some point{/if}.</p>
		{/foldable}
	{/if}
</div>
<div id="the_page" class="account_page">
	<div class="index_holder">
		<div id="page_title">
			<h2>Your Referrals</h2>
		</div>
		<table id="report_table" class="index_table">
			<thead>
				<tr>
					<th>Period</th>
					<th class="numeric">Amount</th>
				</tr>
			</thead>
			<tbody>
				{assign var="total" value="0"}
				{foreach item=period from=$statement}
					<tr>
						<td>{$period.month|to_month} {$period.year}</td>
						<td class="numeric">&pound;{$period.total|pricify}{assign var="total" value="`$total+$period.total`"}</td>
					</tr>
				{foreachelse}
					<tr>
						<td colspan="2">You don't currently have any paid referrals. The easiest way to get some is to get people to signup by visiting <a href="http://www.tactilecrm.com/referral/{$site_address}">your personal referral page</a>.</td>
					</tr>
				{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<th>Total:</th>
					<th class="numeric">&pound;{$total|pricify}</th>
				</tr>
			</tbody>
		</table>
	</div>
</div>
