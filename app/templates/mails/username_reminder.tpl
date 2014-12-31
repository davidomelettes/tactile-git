{if $multiple_users}
We're sorry to hear you forgot your username for Tactile CRM. The email address you searched for belonged to multiple users on your account, so it should be one of the following:
{foreach from=$users item=user name=users}{$user->username}{if !$smarty.foreach.users.last}, {/if}{/foreach}
You can now login at {$login_url}.
{else}
We're sorry to hear you forgot your username for Tactile CRM. It's {$User->username}. You can now use it to login at {$login_url}.
{/if}

If you have any problems logging in, please contact us by emailing support@tactilecrm.com.

--
The Tactile CRM Team
