{literal}{{/literal}{if $flash->hasErrors()}{strip}

"status":"failure","errors":{/literal}{$flash->getErrorsAsJSON()}

{else}

"status":"success","clients":{$clients}

{/strip}{/if}{literal}}{/literal}
