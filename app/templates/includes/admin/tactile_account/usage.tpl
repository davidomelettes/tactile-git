<div id="right_bar">
	{foldable}
		<p><a href="/account/">Back to Account Options</a></p>
	{/foldable}
</div>
<div id="the_page" class="account_page">
	<div class="index_holder">
		<div id="page_title">
			<h2>Your Account Usage</h2>
		</div>
		<div class="content_holder">
			<h3>Current Plan: {$plan->getFormatted('name')}</h3>
			<div class="form_help">
				<p>A <span class="good">green</span> row indicates that you're well within the limit of your account.</p>
				<p>Rows are shown with an <span class="warn">orange</span> background when the amount used is above 90% of the limit.<br />
				<p>Finally, rows are shown as <span class="bad">red</span> when you have reached the limit of your account.</p>
			</div>
			<table id="usage_table" class="index_table">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th class="numeric">Used</th>
						<th class="numeric">Available</th>
						<th class="numeric">% Used</th> 
					</tr>
				</thead>
				<tbody>
					{if $plan->is_per_user()}
					<tr class={$usages.user_limit->getUsage()|percent_of:$account->per_user_limit|threshold:100:90}>
						<td class="label">Enabled Users</td>
						<td class="numeric">{$usages.user_limit->getFormattedUsage()}</td>
						<td class="numeric">{$account->per_user_limit}</td>
						<td class="numeric">{$usages.user_limit->getUsage()|percent_of:$account->per_user_limit}</td>
					</tr>
					{else}
					<tr class={$usages.user_limit->getUsage()|percent_of:$plan->user_limit|threshold:100:90}>
						<td class="label">Enabled Users</td>
						<td class="numeric">{$usages.user_limit->getFormattedUsage()}</td>
						<td class="numeric">{$plan->getFormatted('user_limit')}</td>
						<td class="numeric">{$usages.user_limit->getUsage()|percent_of:$plan->user_limit}</td>
					</tr>
					{/if}
					<tr class="{$usages.contact_limit->getUsage()|percent_of:$plan->contact_limit|threshold:100:90}">
						<td class="label">Contacts</td>
						<td class="numeric">{$usages.contact_limit->getFormattedUsage()}</td>
						<td class="numeric">{$plan->getFormatted('contact_limit')}</td>
						<td class="numeric">{$usages.contact_limit->getUsage()|percent_of:$plan->contact_limit}</td>
					</tr>
					{if $plan->is_per_user()}
					<tr class="{$account->getFileSpaceLimit()|percent_of:$file_allowance|threshold:100:90}">
						<td class="label">File Space</td>
						<td class="numeric">{$usages.file_space->getFormattedUsage()}</td>
						<td class="numeric">{$account->getFileSpaceLimit(true)}</td>
						<td class="numeric">{$usages.file_space->getUsage()|percent_of:$plan->file_space}</td>
					</tr>
					{else}
					<tr class="{$usages.file_space->getUsage()|percent_of:$plan->file_space|threshold:100:90}">
						<td class="label">File Space</td>
						<td class="numeric">{$usages.file_space->getFormattedUsage()}</td>
						<td class="numeric">{$plan->getFormatted('file_space')}</td>
						<td class="numeric">{$usages.file_space->getUsage()|percent_of:$plan->file_space}</td>
					</tr>
					{/if}
					<tr class="{$usages.opportunity_limit->getUsage()|percent_of:$plan->opportunity_limit|threshold:100:90}">
						<td class="label">Open Opportunities</td>
						<td class="numeric">{$usages.opportunity_limit->getFormattedUsage()}</td>
						<td class="numeric">{$plan->getFormatted('opportunity_limit')}</td>
						<td class="numeric">{$usages.opportunity_limit->getUsage()|percent_of:$plan->opportunity_limit}</td>
					</tr>
				</tbody>
			</table>
			{if $plan->is_per_user() && !$plan->is_free()}
			<p>If you are approaching any of these limits, you can <a class="action" href="/users/purchase/">purchase more Users</a>.</p>
			{else}
			<p>If you are approaching any of these limits, you can <a class="action" href="/account/change_plan/">upgrade your account</a>.</p>
			{/if}
		</div>
	</div>
</div>