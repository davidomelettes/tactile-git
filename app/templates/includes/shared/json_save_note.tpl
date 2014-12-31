{strip}
{if $flash->hasErrors()}
{literal}{{/literal}"status":"failure","errors":{$flash->getErrorsAsJSON()}{literal}}{/literal}
{else}
{capture name=note_html}
{include file="elements/timeline/note.tpl"}
{/capture}
{literal}{{/literal}"status":"success","note":{$note_array|@json_encode},"message":"Note Saved Successfully","note_html":{$smarty.capture.note_html|json_encode}{literal}}{/literal}
{/if}
{/strip}