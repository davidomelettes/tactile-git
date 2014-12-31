{assign var=key value=$for|cat:"_addresses"}
{foldable title="Addresses" key=$key add_url_text='Add'}
<ul class="related_list">
	<li>
		<ul class="sidebar_options{if $addresses|count > 1} has_multiple{/if}">
			{foreach from=$addresses item=address name=addresses}
			<li class="{if $smarty.foreach.addresses.last}last{/if}">
				<div class="address{if $address->isMain()} main{/if}" id="{$for}_address_{$address->id}">
					<a class="add_related action edit right">Edit</a>
					<strong class="sprite sprite-address{if $address->isMain()}_main{/if}">{$address->name}</strong> &ndash; <a target="_new" href="{$address->getMapsURL()}">Map</a>
					{$address->toHTML()}
				</div>
			</li>
			{foreachelse}
			<li class="none_yet">You haven't added an address yet, use the add link to add one.</li>
			{/foreach}
		</ul>
	</li>
	{if $for eq 'person' && $Organisation && $organisation_addresses|count > 0}
	<li>
		<ul class="organisation">
			{foreach from=$organisation_addresses item=address name=addresses}
			{if $address->isMain()}
			<li class="last">
				<div class="address" id="organisation_address_{$address->id}">
					<strong><a href="/organisations/view/{$Organisation->id}" class="sprite sprite-organisation">{$Organisation->name}</a></strong> &ndash; <a target="_new" href="{$address->getMapsURL()}">Map</a>
					{$address->toHTML()}
				</div>
			</li>
			{/if}
			{/foreach}
		</ul>
	</li>
	{/if}
</ul>
{/foldable}