{strip}{literal}{{/literal}"status":"success",
"emails":{literal}{{/literal}
{foreach from=$emails item=email name=emails}
"{$email->id}":"{$email->email_address}"{if !$smarty.foreach.emails.last},{/if}
{/foreach}
{literal}}{/literal},
"templates":[
{foreach from=$templates item=template name=templates}
{$template->asJson()}{if !$smarty.foreach.templates.last},{/if}
{/foreach}
],
"user_email":"{$user_email}",
"user_name":"{$user_name}"
{literal}}{/literal}
{/strip}