<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Tactile CRM Email</title>
</head>
<body style="padding: 0; margin: 0; background-color: #EEEEEE;">
	<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#EEEEEE">
		<tr>
			<td align="center">
				<table width="600" cellspacing="0" cellpadding="25">
					<tr>
						<td>
							<table width="550" cellspacing="0" cellpadding="5" bgcolor="#CCCCCC">
								<tr>
									<td>
										<table width="540" cellspacing="25" cellpadding="0" bgcolor="#FFFFFF">
											<tr>
												<td>
													<table width="490" cellspacing="0" cellpadding="0">
														<tr>
															<td width="38"><img src="http://www.tactilecrm.com/graphics/emaillogo.png" width="38" height="50" alt="" border="0" style="padding: 0; margin: 0;" /></td>
															<td valign="middle" width="452"><p style="font-family: Arial; font-size: 24px; font-weight: bold; padding: 0; margin: 0 0 0 25px; color: #333333;">Activity Reminder</p></td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td>
													<table width="490" cellspacing="0" cellpadding="0" style="border-top: 1px solid #CCCCCC;">
														<tr>
															<td>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 25px 0 10px 0; padding: 0;">Hi {$recipient.firstname},</p>

{if $recipient.activities_today|@count > 0}
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">You have {$recipient.activities_today|@count} activities to do today, they are as follows:</p>
<ul style="margin: 0; padding: 0;">
{foreach name=activities item=activity from=$recipient.activities_today}
<li style="list-style-type: square; font-family: Arial; font-size: 12px; color: #333; margin: 0 0 5px 20px; padding: 0;">
{if $activity.class eq 'event'}
<a style="text-decoration: none; color: #333; font-weight: bold;" href="http://{$user_space}.tactilecrm.com/activities/view/{$activity.id}">{$activity.name}</a>{if $activity.time} (this begins at {$activity.time}){/if}
{else}
<a style="text-decoration: none; color: #333; font-weight: bold;" href="http://{$user_space}.tactilecrm.com/activities/view/{$activity.id}">{$activity.name}</a>{if $activity.time} (this needs to be done by {$activity.time}){/if}
{/if}
{if $activity.person}<br /><a style="color: #333; text-decoration: none;" href="http://{$user_space}.tactilecrm.com/people/view/{$activity.person_id}">{$activity.person}</a>
	{if $activity.person_phone || $activity.person_mobile || $activity.person_email}<span style="color: #666;">( 
	{$activity.person_phone}
	{if $activity.person_mobile}{if $activity.person_phone} / {/if}{$activity.person_mobile}{/if}
	{if $activity.person_email}{if $activity.person_phone || $activity.person_mobile} / {/if}<a style="color: #666;" href="mailto:{$activity.person_email}">{$activity.person_email}</a>{/if}
	)</span>{/if}
{/if}
{if $activity.organisation}<br /><a style="color: #333; text-decoration: none;" href="http://{$user_space}.tactilecrm.com/organisations/view/{$activity.organisation_id}">{$activity.organisation}</a>
	{if $activity.organisation_phone || $activity.organisation_mobile || $activity.organisation_email}<span style="color: #666;">( 
	{$activity.organisation_phone}
	{if $activity.organisation_mobile}{if $activity.organisation_phone} / {/if}{$activity.organisation_mobile}{/if}
	{if $activity.organisation_email}{if $activity.organisation_phone || $activity.organisation_mobile} / {/if}<a style="color: #666;" href="mailto:{$activity.organisation_email}">{$activity.organisation_email}</a>{/if}
	)</span>{/if}
{/if}
 </li>
{/foreach}
</ul>
{if $recipient.activities_overdue|@count > 0}
 <p style="font-family: Arial; font-size: 12px; color: #333; margin: 10px 0 10px 0; padding: 0;">In addition, the following activities are overdue (oldest first):</p>
 <ul style="margin: 0; padding: 0;">
{foreach name=activities item=activity from=$recipient.activities_overdue}
<li style="list-style-type: square; font-family: Arial; font-size: 12px; color: #333; margin: 0 0 5px 20px; padding: 0;"><a style="color: #333; text-decoration: none; font-weight: bold;" href="http://{$user_space}.tactilecrm.com/activities/view/{$activity.id}">{$activity.name}</a> - {$activity.date}{if $activity.time} {$activity.time}{/if}</li>
{/foreach}
</ul>
{/if}
{else}
<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">You have the following overdue activities:</p>
<ul style="margin: 0; padding: 0;">
{foreach name=activities item=activity from=$recipient.activities_overdue}
<li style="list-style-type: square; font-family: Arial; font-size: 12px; color: #333; margin: 0 0 5px 20px; padding: 0;"><a style="text-decoration: none; color: #333; font-weight: bold; href="http://{$user_space}.tactilecrm.com/activities/view/{$activity.id}">{$activity.name}</a> - {$activity.date}{if $activity.time} {$activity.time}{/if}</li>
{/foreach}
</ul>
{/if}
																<p style="font-family: Arial; font-size: 12px; color: #999; margin: 0 0 10px 0; padding: 0;">--<br />This is an automated email sent by Tactile CRM - you can configure when and why we send you emails by logging into your account and going to your '<a href="http://{$user_space}.tactilecrm.com/preferences">Preferences</a>'.</p>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
