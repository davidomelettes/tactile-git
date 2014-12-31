{strip}
{if $flash->hasErrors()}
{literal}{{/literal}"status":"failure","errors":{$flash->getErrorsAsJSON()}{literal}}{/literal}
{else}
{literal}{{/literal}"status":"success","message":{$flash->getMessageAsJSON()}{literal}}{/literal}
{/if}
{/strip}