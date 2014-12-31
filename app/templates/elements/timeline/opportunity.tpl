<div class="opportunity{if $opportunity->owner eq $current_user->getRawUsername()} mine{/if}">
	<div class="type round-left">Opp</div>
	<div class="hbf">
		{if $opportunity->organisation_id || $opportunity->person_id}
		<div class="attached">
			{include file="elements/timeline/attached_things.tpl" parent=$opportunity}
		</div>
		{/if}
		<div class="header" title="An Opportunity">
			<h4 title="{$opportunity->getTimelineSubject()|escape}"><a href="{$opportunity->getTimelineURL()}">{$opportunity->getTimelineSubject()|truncate:60:'...'|escape}</a></h4>
		</div>
		<div class="body">
			{if $opportunity->getTimelineBody()}
			<p>{$opportunity->getTimelineBody()}</p>
			{/if}
		</div>
		<div class="footer">
			<div class="owner_time">
				{$opportunity->getTimelineWhenString()}
			</div>
		</div>
	</div>
</div>