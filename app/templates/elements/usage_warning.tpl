{assign var="days" value=$current_user->getAccount()->account_age_days()}
{if $display_usage_warning}
{foldable key="usage_warning" title="You've reached your account limit" extra_class="usage_warning"}
<p>
{foreach from=$usage_warnings key=type item=count}
	{$type|ucwords}: {$count.used}/{$count.limit}<br />
{/foreach}
</p>
<p>
	To add more {$usage_warning_types} you need to <a href="/account/change_plan/">upgrade your account</a>.
</p>
{/foldable}
{elseif !$upsell && $current_user->getAccount()->is_free() && $days>2}
<div class="foldable usage_warning">
	<div>
		{if $current_user->getAccount()->in_trial()}
		<p>You are currently on day <strong>{$days} of your 14 day trial</strong> and have full access to themes, 3rd party integrations and reports.</p>
		<p>After the trial period Tactile CRM will continue to work but these features will be disabled.</p>
		{else}
		<p><strong>Your trial period has now finished</strong> so 3rd party integrations, themes and reports are no longer be accessible.</p>
		{/if}
		<p>You can <strong><a href="/account/change_plan/">upgrade your account</a></strong> to get unlimited contacts, opportunities, 3rd party integration and 10GB of file storage.</p>
	</div>
</div>
{/if}