{if $flash->hasErrors()}
{literal}
{"status":"failure","errors":{/literal}{$flash->getErrorsAsJSON()}{literal}}
{/literal}
{else}
{literal}
{"status":"success","message":{/literal}{$flash->getMessagesAsJSON()}{if $id},"id":{$id}{/if}{literal}}
{/literal}
{/if}