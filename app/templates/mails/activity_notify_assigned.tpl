We just wanted to let you know that {$Activity->getFormatted('assigned_by')} has assigned an activity for you to complete. A summary follows:

Activity: {$Activity->name}
{if $Activity->isEvent()}
Starts: {$Activity->date_string()}
Ends: {$Activity->end_date_string()}
{else}
Due: {$Activity->date_string()}
{/if}

Link: http://{$smarty.server.HTTP_HOST}/activities/view/{$Activity->id}

--
This is an automated email sent by Tactile CRM - you can configure when and why we send you emails by logging into your account and going to the 'Preferences' page.
