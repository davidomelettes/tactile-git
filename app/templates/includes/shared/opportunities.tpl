{if $opportunities|count > 0}
<ul class="related_list">
{foreach name=opportunities item=opportunity from=$opportunities}
	<li{if $smarty.foreach.opportunities.last} class="none_yet"{/if}>
		<a href="/opportunities/view/?id={$opportunity->id}" class="view_link sprite sprite-opportunity">{$opportunity->name|default:"-"}</a>
		<span class="small"><a class="action" href="/opportunities/edit/?id={$opportunity->id}">Edit</a></span>
		<dl>
			{if $opportunity->status neq ""}<dt>Status</dt><dd>{$opportunity->status}</dd><br />{/if}
			{if $opportunity->cost neq ""}<dt>Value</dt><dd>{$opportunity->getFormatted('cost')}</dd>{/if}
		</dl>
		<div class="c-left"></div>
	</li>	
{/foreach}
</ul>
<p><a class="action" href="/opportunities/archived/?{$attached_to|strtolower}_id={$attached_id}">Check for Archived Opportunities</a></p>
{else}
<p class="empty">None at present -  check for <a href="/opportunities/archived/?{$attached_to|strtolower}_id={$attached_id}">archived ones</a>.</p>
{/if}
