{literal}{{/literal}"status":"success", "opportunities": [{strip}
{foreach name=opportunities item=opportunity from=$opportunitys}
{$opportunity->asJson()}
{if !$smarty.foreach.opportunities.last},{/if}
{/foreach}
{/strip}], "cur_page":{$cur_page}, "num_pages":{$num_pages}, "per_page":{$opportunitys->per_page}, "total":{$opportunitys->num_records}{literal}}{/literal}