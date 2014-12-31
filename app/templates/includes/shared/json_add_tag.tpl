{if $flash->hasErrors() || !$tagged_item}
{ldelim}"status":"failure","errors":{$flash->getErrorsAsJSON()}{rdelim}
{else}
{ldelim}"status":"success","message":{$flash->getMessageAsJSON()},"tags":{$tagged_item->getTags()|@json_encode}{rdelim}
{/if}