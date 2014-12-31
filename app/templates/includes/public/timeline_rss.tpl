<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
	<channel>
		<title>{$rss_title|default:'My Dashboard - Tactile CRM'}</title>
		<link>http://{php}echo Omelette::getUserspace();{/php}.tactilecrm.com</link>
		<description>{if $restriction eq 'notes_emails'}Recent Notes &amp; Emails{elseif $restriction eq 'notes_emails_acts'}Recent Notes, Emails, &amp; Completed Activities{elseif $restriction eq 'custom'}Recent Activity (Custom){/if}</description>
		{foreach from=$activity_timeline item=item}
		<item>
			<title>{$item->getTimelineType()}: {$item->getTimelineSubject()|h}</title>
			<description>{$item->getTimelineBody()|h:0:false}</description>
			<link>http://{php}echo Omelette::getUserspace();{/php}.tactilecrm.com{$item->getTimelineURL()}</link>
			<category>{$item->getTimelineType()}</category>
			<pubDate>{$item->getTimelineTime()|date_format:'%a, %d %b %Y %H:%M:%S %z'}</pubDate>
			<guid>{$item->getTimelineType()}_{$item->id}</guid>
		</item>
		{/foreach}
	</channel>
</rss>