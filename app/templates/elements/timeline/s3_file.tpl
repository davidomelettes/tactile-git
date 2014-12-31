<div class="file{if $s3_file->owner eq $current_user->getRawUsername()} mine{/if}">
	<div class="type round-left">File</div>
	<div class="hbf">
		{if $s3_file->organisation_id || $s3_file->person_id || $s3_file->opportunity_id || $s3_file->activity_id}
		<div class="attached">
			{include file="elements/timeline/attached_things.tpl" parent=$s3_file}
		</div>
		{/if}
		<div class="header" title="A File">
			<h4 title="{$s3_file->getTimelineSubject()|escape}">{$s3_file->getTimelineSubject()|truncate:60|escape}</h4>
			{if $s3_file->owner eq $current_user->getRawUsername()}
			<div class="actions">
				<ul>
					<li><a href="/files/delete/{$s3_file->id}" class="action delete">Delete</a></li>
				</ul>
			</div>
			{/if}
		</div>
		<div class="body">
			<span class="download_link"><a href="{$s3_file->getTimelineURL()}" class="sprite sprite-download">Download</a> ({$s3_file->size|bytes})</span>
		</div>
		<div class="footer">
			<div class="owner_time">
				{$s3_file->getTimelineWhenString()}
			</div>
		</div>
	</div>
</div>
