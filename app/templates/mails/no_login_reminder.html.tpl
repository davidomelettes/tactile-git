{assign var='email_title' value='Getting Started with Tactile CRM'}
{include file="mails/shared/header.tpl"}
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 25px 0 10px 0; padding: 0;">Hi {$firstname},</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">As you don't seem to have had a chance to use Tactile CRM yet I was wondering if there is anything I can do to help?</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">My contact details are in the signature below if you have any questions, and I have included your account details below as sometimes they get filed as junk:</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">Login at: <strong><a style="color: #00C;" href="http://{$site_address}.tactilecrm.com">http://{$site_address}.tactilecrm.com</a></strong><br />Username: <strong>{$username}</strong></p>
{include file="mails/shared/jake-sig.html.tpl"}
{include file="mails/shared/footer.tpl"}
