{strip}

{if $flash->hasErrors()}
{literal}
{"status":"failure","errors":{/literal}{$flash->getErrorsAsJSON()}{literal}}
{/literal}

{else}
{literal}
{"status":"success","message":{/literal}{$flash->getMessageAsJSON()}{literal},
"addresses":[{/literal}
{foreach from=$addresses item=address name=addresses}
{$address->asJSON()}{if !$smarty.foreach.addresses.last},{/if}
{/foreach}
]
{literal}}{/literal}
{/if}

{/strip}