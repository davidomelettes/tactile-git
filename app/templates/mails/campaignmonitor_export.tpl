Your export to Campaign Monitor has completed. Of the {$total} People selected, we were able to subscribe {if $total == $successes}all{else}{$successes}{/if} of them to the list called "{$list}".

{foreach from=$messages item=count key=msg}
We were unable to subscribe {$count} {if $count > 1}People{else}Person{/if} because of the following problem: {$msg}
{/foreach}

--
The Tactile CRM Team
