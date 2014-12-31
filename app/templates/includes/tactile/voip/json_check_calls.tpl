{if $flash->hasErrors()}
{literal}
{status:'failure','errors':{/literal}{$flash->getErrorsAsJSON()}{literal}}
{/literal}
{else}
{literal}
{'status':'success', 'matches': {/literal}{$matches|@json_encode}{literal}}
{/literal}
{/if}