{if $flash->hasErrors()}
{literal}
{"status":"failure","errors":{/literal}{$flash->getErrorsAsJSON()}{literal}}
{/literal}
{else}
{literal}
{"status":"success","id":{/literal}{$contact->id}{literal},"message":"Saved Successfully","contact":{/literal}{$contact->toJSON()}{literal}}
{/literal}
{/if}