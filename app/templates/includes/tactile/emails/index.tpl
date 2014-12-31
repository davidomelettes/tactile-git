<div id="right_bar">
	{foldable key="dropbox_info" title="Dropbox Information"}
		{if $dropboxkey neq ''}
		<p><a class="sprite sprite-download" href="/emails/dropbox_vcard">Download Dropbox address as VCard</a></p>
		{else}
		<p>You have not yet set a Dropbox key.</p>
		{/if}
	{/foldable}
	{foldable key="emails_help" title="Emails Help" extra_class="help"}
		<p>Use this area of Tactile to view emails which have been processed by your dropbox.</p>
	{/foldable}
	{*include file="elements/my_email_addresses.tpl"*}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/paging.tpl" for="emails"}
			<h2>
				{if $restriction eq 'all'}
				All Emails
				{elseif $restriction eq 'unassigned'}
				Unassigned Emails
				{elseif $restriction eq 'incoming'}
				Incoming Emails
				{elseif $restriction eq 'outgoing'}
				Outgoing Emails
				{/if}
			</h2>
		</div>
		<div id="page_main">
			<div class="content_holder">
				<div class="form_help">
					<p>To attach to a contact, forward incoming or BCC outgoing email to:
					<a href="mailto:{$current_user->getDropboxAddress()}" title="{$current_user->getDropboxAddress()}">{$current_user->getDropboxAddress()}</a>
					</p>
				</div>
				<table id="email_index" class="index_table">
					<thead>
						<tr>
							<th>Subject</th>
							<th>Received</th>
							<th>Person</th>
							<th>Company</th>
							<th>Opportunity</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th>Subject</th>
							<th>Received</th>
							<th>Person</th>
							<th>Company</th>
							<th>Opportunity</th>
							<th>&nbsp;</th>
						</tr>
					</tfoot>
					<tbody>
						{foreach name=emails item=email from=$emails}
						<tr>
							<td class="primary"><a href="/emails/assign/{$email->id}">{$email->subject|escape}</a></td>
							<td>{$email->getFormatted('created')}</td>
							<td>{if $email->person_id}<a href="/people/view/{$email->person_id}">{$email->person}</a>{/if}</td>
							<td>{if $email->organisation_id}<a href="/organisations/view/{$email->organisation_id}">{$email->organisation}</a>{/if}</td>
							<td>{if $email->opportunity_id}<a href="/opportunities/view/{$email->opportunity_id}">{$email->opportunity}</a>{/if}</td>
							<td class="t-right"><div class="email_actions"><a href="/emails/delete/{$email->id}" class="action delete">Delete</a></div></td>
						</tr>
						{foreachelse}
						<tr>
							<td colspan="6" class="empty_table">No Emails to Show</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
				{if $num_pages > 1}
				<div class="bottom_paging">
					{include file="elements/paging.tpl" for="emails"}
				</div>
				{/if}
			</div>
		</div>
	</div>
</div>
