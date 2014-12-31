{if $flash->hasErrors()}
{literal}
{"status":"failure","errors":{/literal}{$flash->getErrorsAsJSON()}{literal}}
{/literal}
{else}
{if $no_clients}
{literal}
{"status":"success","no_clients": true}
{/literal}
{else}
{literal}
{"status":"success","clients":{/literal}{$clients|@json_encode}{literal}}
{/literal}
{/if}
{/if}