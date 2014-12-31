{assign var='email_title' value='Your Account Details'}
{include file="mails/shared/header.tpl"}
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 25px 0 10px 0; padding: 0;">Dear {$Person->firstname},</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">You've had an account set up on Tactile CRM - the easy to use contact and sales management system. You can login at <strong><a href="http://{$user_space}.tactilecrm.com">http://{$user_space}.tactilecrm.com</a></strong> using the following details:</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">Username: <strong>{$username}</strong><br/>Password: <strong>{$password}</strong></p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">If you have any problems logging in, please contact us by emailing our support team on <a href="mailto:support@tactilecrm.com?Subject=Login Issues">support@tactilecrm.com</a> and we'll get back to you ASAP.</p>
{include file="mails/shared/team-sig.html.tpl"}
{include file="mails/shared/footer.tpl"}
