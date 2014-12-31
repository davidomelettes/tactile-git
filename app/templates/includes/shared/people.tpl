<ul class="related_list">
{foreach name=people item=person from=$people}
	<li{if $smarty.foreach.people.last} class="none_yet"{/if}>
		<a href="/people/view/?id={$person->id}" class="view_link sprite sprite-person">{$person->fullname}</a>
		{if $person->jobtitle neq ''}<span class="jobtitle">({$person->jobtitle})</span>{/if}<span class="small"><a class="action" href="/people/edit/?id={$person->id}">Edit</a></span>
		{if $person->phone neq "" || $person->mobile neq "" || $person->email neq "" || $person->fax neq ""}
		<ul class="sidebar_options">
			{if $person->email neq ""}<li class="email"><a class="sprite sprite-email email" href="mailto:{$person->email|escape|urlencode}{if $current_user->getDropboxAddress()}?bcc={$current_user->getDropboxAddress()|urlencode}{/if}">{$person->email|default:"-"}</a></li>{/if}
			{if $person->phone neq ""}<li class="phone"><span class="sprite sprite-phone">{$person->phone|default:"-"}</span></li>{/if}
			{if $person->mobile neq ""}<li class="mobile"><span class="sprite sprite-mobile">{$person->mobile|default:"-"}</span></li>{/if}
			{if $person->fax neq ""}<li class="fax"><span class="sprite sprite-fax">{$person->fax|default:"-"}</span></li>{/if}
		</ul>
		{/if}
	</li>
{foreachelse}
	<li class="none_yet">No people have been added yet.</li>
{/foreach}
</ul>
