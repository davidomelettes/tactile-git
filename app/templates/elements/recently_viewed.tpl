{foldable key="recently_viewed" title="Recently Viewed Items"}
	<ul class="sidebar_options recently_viewed">
	{foreach name=recent item=page from=$recently_viewed}
		<li><a class="sprite sprite-{$page->getType()}" href="{$page->getURL()}">{$page->getLabel()|escape}</a></li>
	{foreachelse}
		<li>No items to display</li>
	{/foreach}
	</ul>
{/foldable}