{if $flash->hasErrors()}
{literal}
{"status":"failure","errors":{/literal}{$flash->getErrorsAsJSON()}{literal}}
{/literal}
{else}
{literal}
{"status":"success","email":"{/literal}{$email}{literal}"}
{/literal}
{/if}