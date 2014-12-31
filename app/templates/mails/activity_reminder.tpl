Hi {$recipient.firstname},

{if $recipient.activities_today|@count > 0}
You have {$recipient.activities_today|@count} activities to do today, they are as follows:

{foreach name=activities item=activity from=$recipient.activities_today}
{if $activity.class eq 'event'}
* {$activity.name}{if $activity.time} (this begins at {$activity.time}){/if} (http://{$user_space}.tactilecrm.com/activities/view/{$activity.id})
{else}
 * {$activity.name}{if $activity.time} (this needs to be done by {$activity.time}){/if} (http://{$user_space}.tactilecrm.com/activities/view/{$activity.id})
 {/if}
{/foreach}

{if $recipient.activities_overdue|@count > 0}
In addition, the following activities are overdue (oldest first):

{foreach name=activities item=activity from=$recipient.activities_overdue}
 * {$activity.name} - {$activity.date}{if $activity.time} {$activity.time}{/if} (http://{$user_space}.tactilecrm.com/activities/view/{$activity.id})
{/foreach}
{/if}
{else}
You have the following overdue activities:
{foreach name=activities item=activity from=$recipient.activities_overdue}
 * {$activity.name} - {$activity.date}{if $activity.time} {$activity.time}{/if} (http://{$user_space}.tactilecrm.com/activities/view/{$activity.id})
{/foreach}
{/if}

--
This email was sent to you by Tactile CRM, you can choose when and why you will be sent emails by changing your Account Preferences at http://{$user_space}.tactilecrm.com/preferences.
