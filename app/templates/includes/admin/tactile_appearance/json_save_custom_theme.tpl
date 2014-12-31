{if $flash->hasErrors()}
{literal}{"status":"failure","errors":{/literal}{$flash->getErrorsAsJSON()}{literal}}{/literal}
{else}
{literal}{"status":"success","message":{/literal}{$flash->getMessagesAsJSON()},"primary":"{$primary}","secondary":"{$secondary}"{literal}}{/literal}
{/if}