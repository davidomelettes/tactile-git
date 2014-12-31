<div class="flag{if $flag->owner eq $current_user->getRawUsername()} mine{/if}">
	<div class="type round-left">Flag</div>
	<div class="hbf">
		<div class="header" title="A Flag">
			<h4>{$flag->getTimelineSubject()}</h4>
		</div>
		<div class="footer">
			<div class="dropbox_time">
				{$flag->getTimelineWhenString()}
			</div>
		</div>
	</div>
</div>
