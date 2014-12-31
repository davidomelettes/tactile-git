{if $flash->hasErrors()}
{literal}
{"status":"failure","errors":{/literal}{$flash->getErrorsAsJSON()}{literal}}
{/literal}
{else}
{literal}
{"status":"success","message":{/literal}{$flash->getMessagesAsJSON()}{if $model->id},"id":{$model->id}{/if}{literal}}
{/literal}
{/if}