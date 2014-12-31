<div class="{if !$email->organisation_id && !$email->person_id && !$email->opportunity_id}unassigned_{/if}email{if $email->owner eq $current_user->getRawUsername()} mine{/if}">
	<div class="type round-left">Email</div>
	<div class="hbf">
		{if $email->organisation_id || $email->person_id || $email->opportunity_id}
		<div class="attached">
			{include file="elements/timeline/attached_things.tpl" parent=$email}
		</div>
		{/if}
		<div class="header">
			<h4 title="{$email->getTimelineSubject()|escape}">{$email->getTimelineSubject()|escape|truncate:60}</h4>
			{if $email->owner eq $current_user->getRawUsername()}
			<div class="actions">
				<ul>
					<li><a href="/emails/assign/{$email->id}" class="action">Assign</a></li>
					<li><a href="/emails/delete/{$email->id}" class="action delete">Delete</a></li>
				</ul>
			</div>
			{/if}
		</div>
		<div class="body">
			<p>
				{if $email->direction eq ''}
				<strong>To:</strong> <a href="mailto:{$email->email_to|urlencode}{if $current_user->getDropboxAddress()}?bcc={$current_user->getDropboxAddress()|urlencode}{/if}">{$email->email_to|escape}</a><br />
				<strong>From:</strong> <a href="mailto:{$email->email_from|urlencode}{if $current_user->getDropboxAddress()}?bcc={$current_user->getDropboxAddress()|urlencode}{/if}">{$email->email_from|escape}</a>
				{elseif $email->direction eq 'outgoing'}
				<strong>To:</strong> <a href="mailto:{$email->email_to|urlencode}{if $current_user->getDropboxAddress()}?bcc={$current_user->getDropboxAddress()|urlencode}{/if}">{$email->email_to|escape}</a>
				{else}
				<strong>From:</strong> <a href="mailto:{$email->email_from|urlencode}{if $current_user->getDropboxAddress()}?bcc={$current_user->getDropboxAddress()|urlencode}{/if}">{$email->email_from|escape}</a>
				{/if}
			</p>
			{assign var=email_body value=$email->getTimelineBody()}
			{if $email_body|strlen > 300}
			<p class="body_content">
				{$email_body|truncate:200:'...':false}<br />
				<a class="body_toggle action">[Show More]</a>
			</p>
			<p class="body_content full" style="display:none;">
				{$email_body}<br />
				<a class="body_toggle action">[Show Less]</a>
			</p>
			{else}
				<p class="body_content full">
				{$email_body}
				</p>
			{/if}
		</div>
		<div class="footer">
			<div class="dropbox_time">
				{$email->getTimelineWhenString()}
			</div>
		</div>
		{if $email->email_attachments > 0}
		<div class="email_files">
			<p><span class="sprite sprite-file">{$email->email_attachments} Attachment{if $email->email_attachments > 1}s{/if} (<a>Show</a>)</span></p>
		</div>
		{/if}
	</div>
</div>
