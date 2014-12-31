{if $flash->hasErrors()}
{literal}
{"status":"failure","errors":{/literal}{$flash->getErrorsAsJSON()}{literal}}
{/literal}
{else}
{literal}
{"status":"success","message":"Saved Successfully","existing_custom_fields":{/literal}{$existing_custom_fields_json}{literal}}
{/literal}
{/if}