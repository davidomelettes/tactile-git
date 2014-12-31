{if $flash->hasErrors()}
{literal}
{"status":"failure","errors":{/literal}{$flash->getErrorsAsJSON()}{literal}}
{/literal}
{else}
{literal}
{"status":"success","message":"Saved Successfully","contacts":[{/literal}{foreach from=$contacts item=contact name=contacts}{$contact->asJson()}{if !$smarty.foreach.contacts.last},{/if}{/foreach}{literal}]}
{/literal}
{/if}