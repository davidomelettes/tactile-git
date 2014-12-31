{literal}{{/literal}"status":"success",{strip}
"thumbnails":{literal}{{/literal}{foreach name=tns from=$tns item=url key=id}
"{$id}":"{$url}"{if !$smarty.foreach.tns.last},{/if}
{/foreach}
{/strip}{literal}}}{/literal}