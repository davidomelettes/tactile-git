{literal}{{/literal}"status":"success", "activities": [{strip}
{foreach name=activities item=activity from=$activitys}
{$activity->asJson()}
{if !$smarty.foreach.activities.last},{/if}
{/foreach}
{/strip}], "cur_page":{$cur_page}, "num_pages":{$num_pages}, "per_page":{$activitys->per_page}, "total":{$activitys->num_records}{literal}}{/literal}