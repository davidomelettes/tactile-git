{strip}{literal}{{/literal}{if $flash->hasErrors()}
"status":"failure",
"errors":{$flash->getErrorsAsJSON()}

{else}
"status":"success",
"activity_track":{$track->asJson()},
"activity_track_options":{literal}{{/literal}
"assigned_to":{$assigned_to_options},
"type":{$type_options}
{literal}}{/literal}

{/if}
{literal}}{/literal}{/strip}