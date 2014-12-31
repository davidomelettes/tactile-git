{if $Opportunity}
{assign var=dropbox_action value='opp+'|cat:$Opportunity->id}
{else}
{assign var=dropbox_action value='dropbox'}
{/if}
{if ($contact_methods|count > 0) || ($organisation_contact_methods|count > 0) || ($person_contact_methods|count > 0) || $for eq 'person' || $for eq 'organisation'}
{assign var=key value=$for|cat:"_contact_methods"}
{if ($for eq 'person') || ($for eq 'organisation')}
	{assign var=update_text value="Update"}
{else}
	{assign var=update_text value=""}
{/if}
{foldable title="Contact Information" key=$key add_url_text=$update_text}
<ul class="related_list">
	{if $Person->id || $for eq 'person' || $for eq 'organisation'}
	<li{if !$organisation_contact_methods|count > 0} class="last"{/if}>
		{if $for neq 'person'}
		<strong><a href="/people/view/{$Person->id}">{$Person->name}</a></strong>
		{elseif $person_contact_methods|count > 0}
		<strong>{$Person->name}</strong>
		{/if}
		<div id="{$for}_contact_method_items">
			<ul class="sidebar_options">
			{foreach item=contact from=$contact_methods name=contact_methods}
				{if $contact->type eq 'E'}
					<li class="email{if $contact->is_main()} main{/if}{if $smarty.foreach.contact_methods.last} last{/if}" id="contact_method_{$contact->id}">
						<a class="sprite sprite-email" href="mailto:{$contact->contact|escape}{if $current_user->getDropboxAddress()}?bcc={$current_user->getDropboxAddress($dropbox_action)}{/if}">{$contact->contact|escape}</a>
				{elseif $contact->type eq 'T'}
					<li class="phone{if $contact->is_main()} main{/if}{if $smarty.foreach.contact_methods.last} last{/if}" id="contact_method_{$contact->id}">
						<span class="sprite sprite-phone">{if $ipscape_site_address}<a href="http://{$ipscape_site_address}.ipscape.co.uk/crmtoolbar/dial_contact.php?contactid={$Person->id}&amp;ph1={$contact->contact|replace:" ":""}">{$contact->contact|escape}</a>{else}{$contact->contact|escape}{/if}</span>
				{elseif $contact->type eq 'M'}
					<li class="mobile{if $contact->is_main()} main{/if}{if $smarty.foreach.contact_methods.last} last{/if}" id="contact_method_{$contact->id}">
						<span class="sprite sprite-mobile">{$contact->contact|escape}</span>
				{elseif $contact->type eq 'F'}
					<li class="fax{if $contact->is_main()} main{/if}{if $smarty.foreach.contact_methods.last} last{/if}" id="contact_method_{$contact->id}">
						<span class="sprite sprite-fax">{$contact->contact|escape}</span>
				{elseif $contact->type eq 'W'}
					<li class="website{if $contact->is_main()} main{/if}{if $smarty.foreach.contact_methods.last} last{/if}" id="contact_method_{$contact->id}">
						{$contact->getFormatted('contact', 'OmeletteURLFormatter')}
				{elseif $contact->type eq 'I'}
					<li class="twitter{if $contact->is_main()} main{/if}{if $smarty.foreach.contact_methods.last} last{/if}" id="contact_method_{$contact->id}">
						<a class="sprite sprite-twitter" href="http://twitter.com/{$contact->contact|escape}">@{$contact->contact|escape}</a>
				{elseif $contact->type eq 'S'}
					<li class="skype{if $contact->is_main()} main{/if}{if $smarty.foreach.contact_methods.last} last{/if}" id="contact_method_{$contact->id}">
						<img src="http://mystatus.skype.com/smallicon/{$contact->contact}" width="16" height="16" alt="My status" /> <a href="skype:{$contact->contact|escape}?chat">{$contact->contact|escape}</a>
				{elseif $contact->type eq 'L'}
					<li class="linkedin{if $contact->is_main()} main{/if}{if $smarty.foreach.contact_methods.last} last{/if}" id="contact_method_{$contact->id}">
						<a class="sprite sprite-linkedin" href="http://www.linkedin.com/in/{$contact->contact|escape}">{$contact->contact|escape}</a>
				{elseif $contact->type eq 'K'}
					<li class="facebook{if $contact->is_main()} main{/if}{if $smarty.foreach.contact_methods.last} last{/if}" id="contact_method_{$contact->id}">
						<a class="sprite sprite-facebook" href="http://www.facebook.com/{$contact->contact|escape}">{$contact->contact|escape}</a>
				{/if}
					{if $contact->name} <span class="name">({$contact->name|escape})</span>{/if}
				</li>
			{foreachelse}
				{if $for eq 'person' OR $for eq 'organisation'}<li class="none_yet">You haven't added any contact information yet, use the update link to add some.</li>{/if}
	
			{/foreach}
			</ul>
		</div>
	</li>
	{/if}
	
	{if $organisation_contact_methods|count > 0}
		{foreach name=organisation_contact_methods item=contact from=$organisation_contact_methods}
			{if $smarty.foreach.organisation_contact_methods.first && $Person->organisation_id}
			<li>
				<p><strong><a href="/organisations/view/{$Person->organisation_id}" class="sprite sprite-organisation">{$Person->organisation}</a></strong></p>
				<ul class="sidebar_options">
			{elseif $smarty.foreach.organisation_contact_methods.first}
			<li>
				<p><strong><a href="/organisations/view/{$Organisation->id}">{$Organisation->name}</a></strong></p>
				<ul class="sidebar_options">
			{/if}
			
			{if $contact->type eq 'E'}
				<li class="email{if $contact->is_main()} main{/if}" id="organisation_method_{$contact->id}">
					<a class="sprite sprite-email" href="mailto:{$contact->contact|escape}{if $current_user->getDropboxAddress($dropbox_action)}?bcc={$current_user->getDropboxAddress()}{/if}">{$contact->contact|escape}</a>
			{elseif $contact->type eq 'T'}
				<li class="phone{if $contact->is_main()} main{/if}" id="organisation_method_{$contact->id}">
					<span class="sprite sprite-phone">{$contact->contact|escape}</span>
			{elseif $contact->type eq 'M'}
				<li class="mobile{if $contact->is_main()} main{/if}" id="organisation_method_{$contact->id}">
					<span class="sprite sprite-mobile">{$contact->contact|escape}</span>
			{elseif $contact->type eq 'F'}
				<li class="fax{if $contact->is_main()} main{/if}" id="organisation_method_{$contact->id}">
					<span class="sprite sprite-fax">{$contact->contact|escape}</span>
			{elseif $contact->type eq 'W'}
				<li class="website{if $contact->is_main()} main{/if}" id="organisation_method_{$contact->id}">
					{$contact->getFormatted('contact', 'OmeletteURLFormatter')}
			{elseif $contact->type eq 'I'}
				<li class="twitter{if $contact->is_main()} main{/if}" id="organisation_method_{$contact->id}">
					<a class="sprite sprite-twitter" href="http://twitter.com/{$contact->contact|escape}">@{$contact->contact|escape}</a>
			{elseif $contact->type eq 'S'}
				<li class="skype{if $contact->is_main()} main{/if}" id="organisation_method_{$contact->id}">
					<img src="http://mystatus.skype.com/smallicon/{$contact->contact}" width="16" height="16" alt="My status" /> <a href="skype:{$contact->contact|escape}?chat">{$contact->contact|escape}</a>
			{elseif $contact->type eq 'L'}
				<li class="linkedin{if $contact->is_main()} main{/if}" id="organisation_method_{$contact->id}">
					<a class="sprite sprite-linkedin" href="http://www.linkedin.com/in/{$contact->contact|escape}">{$contact->contact|escape}</a>
			{elseif $contact->type eq 'K'}
				<li class="facebook{if $contact->is_main()} main{/if}" id="organisation_method_{$contact->id}">
					<a class="sprite sprite-facebook" href="http://www.facebook.com/{$contact->contact|escape}">{$contact->contact|escape}</a>
			{/if}
				{if $contact->name} ({$contact->name|escape}){/if}
				</li>
			{if $smarty.foreach.organisation_contact_methods.last}
				</ul>
			</li>
			{/if}
		{/foreach}
	{/if}
</ul>
{/foldable}
{/if}
