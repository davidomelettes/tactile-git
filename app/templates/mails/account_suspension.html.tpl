{assign var='email_title' value='Your Account Has Been Suspended'}
{assign var='email_border' value='FF3300'}
{include file="mails/shared/header.tpl"}
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 25px 0 10px 0; padding: 0;">Hi {$account->firstname},</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">We've tried to take payment for your subscription several times but don't seem to be able to. As a precautionary measure we have suspended your account.</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">To re-enable it, simple <strong><a href="https://{$account->site_address}.tactilecrm.com/">login and update the card details</a></strong> when requested.</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">If you think this was in error please contact us at support@tactilecrm.com and we'll do our best to help you out.</p>
{include file="mails/shared/team-sig.html.tpl"}
{include file="mails/shared/footer.tpl"}
