{if $flash->hasErrors()}
{literal}
{"status":"failure","errors":{/literal}{$flash->getErrorsAsJSON()}{literal}}
{/literal}
{else}
{strip}
{literal}{{/literal}
"status":"success",
"message":{$flash->getMessagesAsJSON()}
{if $model->id},"id":{$model->id}{/if}
{if $models},"models":[{foreach from=$models item=model name=models}{$model->id}{if !$smarty.foreach.models.last},{/if}{/foreach}]{/if}
{literal}}{/literal}
{/strip}
{/if}