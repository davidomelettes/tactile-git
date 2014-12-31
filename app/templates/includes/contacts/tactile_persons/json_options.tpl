{literal}{{/literal}"status":"success","person_options":{literal}{{/literal}{strip}
"assigned_to":{literal}{{/literal}
{foreach name=options from=$assigned_to item=option key=id}"{$option}":"{$option}"
{if !$smarty.foreach.options.last},{/if}
{/foreach}
{literal}}{/literal},
"language_code":{literal}{{/literal}
{foreach name=options from=$language_code item=option key=id}"{$id}":"{$option}"
{if !$smarty.foreach.options.last},{/if}
{/foreach}
{literal}}{/literal},
"country_code":{literal}{{/literal}
{foreach name=options from=$country_code item=option key=id}"{$id}":"{$option}"
{if !$smarty.foreach.options.last},{/if}
{/foreach}
{literal}}{/literal},
"default_country_code":"{$default_country_code}"
{/strip}{literal}}}{/literal}