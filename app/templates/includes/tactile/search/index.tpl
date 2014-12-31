{assign var=org_results value=false}
{assign var=people_results value=false}

{if $organisations_by_accountnumber|@count}
<div><h2>Organisations</h2>
<h3><a href="/organisations/search/?accountnumber={$query|urlencode}">by Account Number{if $results_to_display.organisations_by_accountnumber < $organisations_by_accountnumber|@count} ({$results_to_display.organisations_by_accountnumber}/{$organisations_by_accountnumber|@count}){/if}</a></h3></div>
{assign var=org_results value=true}
<ul class="l3">
	{foreach from=$organisations_by_accountnumber item=item name=orgs_acnum}
	{if $smarty.foreach.orgs_acnum.iteration <= $results_to_display.organisations_by_accountnumber}
	<li><a href="/organisations/view/{$item->id}" title="{$item->name}"><span class="sprite sprite-organisation">{$item->name|truncate:30:'...':false}</span></a></li>
	{/if}
	{/foreach}
</ul>
{/if}

{if $organisations_by_email|@count}
<div>{if !$org_results}<h2>Organisations</h2>{/if}
<h3><a href="/organisations/search/?email={$query|urlencode}">by Email Address{if $results_to_display.organisations_by_email < $organisations_by_email|@count} ({$results_to_display.organisations_by_email}/{$organisations_by_email|@count}){/if}</a></h3></div>
{assign var=org_results value=true}	
<ul class="l3">
	{foreach from=$organisations_by_email item=item name=orgs_email}
	{if $smarty.foreach.orgs_email.iteration <= $results_to_display.organisations_by_email}
	<li><a href="/{$organisations}/view/{$item->id}" title="{$item->name}"><span class="sprite sprite-organisation">{$item->name|truncate:30:'...':false}</span></a></li>
	{/if}
	{/foreach}
</ul>
{/if}

{if $organisations|@count}
<div>{if !$org_results}<h2>Organisations</h2>{/if}
<h3><a href="/organisations/search/?name={$query|urlencode}*">by Name{if $results_to_display.organisations < $organisations|@count} ({$results_to_display.organisations}/{$organisations|@count}){/if}</a></h3></div>
{assign var=org_results value=true}
<ul class="l3">
	{foreach from=$organisations item=item key=id name=orgs}
	{if $smarty.foreach.orgs.iteration <= $results_to_display.organisations}
	<li><a href="/{$item.type}/view/{$id}" title="{$item.name}"><span class="sprite sprite-organisation">{$item.name|truncate:30:'...':false}</span></a></li>
	{/if}
	{/foreach}
</ul>
{/if}
	
{if $people_by_email|@count}
<div><h2>People</h2>
<h3><a href="/people/search/?email={$query|urlencode}">by Email Address{if $results_to_display.people_by_email < $people_by_email|@count} ({$results_to_display.people_by_email}/{$people_by_email|@count}){/if}</a></h3></div>
{assign var=people_results value=true}
<ul class="l3">
	{foreach from=$people_by_email item=item name=people_email}
	{if $smarty.foreach.people_email.iteration <= $results_to_display.people_by_email}
	<li><a href="/people/view/{$item->id}" title="{$item->name}"><span class="sprite sprite-person">{$item->name|truncate:30:'...':false}</span></a></li>
	{/if}
	{/foreach}
</ul>
{/if}
	
{if $people_by_surname|@count}
<div>{if !$people_results}<h2>People</h2>{/if}
<h3><a href="/people/search/?surname={$query|urlencode}*">by Surname{if $results_to_display.people_by_surname < $people_by_surname|@count} ({$results_to_display.people_by_surname}/{$people_by_surname|@count}){/if}</a></h3></div>
{assign var=people_results value=true}
<ul class="l3">
	{foreach from=$people_by_surname item=item name=people_surname}
	{if $smarty.foreach.people_surname.iteration <= $results_to_display.people_by_surname}
	<li><a href="/people/view/{$item->id}" title="{$item->name}"><span class="sprite sprite-person">{$item->name|truncate:30:'...':false}</span></a></li>
	{/if}
	{/foreach}
</ul>
{/if}
	
{if $people|@count}
<div>{if !$people_results}<h2>People</h2>{/if}
<h3><a href="/people/search/?firstname={$query|urlencode}*">by Full Name{if $results_to_display.people < $people|@count} ({$results_to_display.people}/{$people|@count}){/if}</a></h3></div>
{assign var=people_results value=true}
<ul class="l3">
	{foreach from=$people item=item key=id name=people}
	{if $smarty.foreach.people.iteration <= $results_to_display.people}
	<li><a href="/{$item.type}/view/{$id}" title="{$item.name}"><span class="sprite sprite-person">{$item.name|truncate:30:'...':false}</span></a></li>
	{/if}
	{/foreach}
</ul>
{/if}
	
{if $opportunities|@count}
<div><h2>Opportunities</h2>
<h3><a href="/opportunities/search/?name={$query|urlencode}*">by Name{if $results_to_display.opportunities < $opportunities|@count} ({$results_to_display.opportunities}/{$opportunities|@count}){/if}</a></h3></div>
{assign var=opp_results value=true}
<ul class="l3">
	{foreach from=$opportunities item=item key=id name=opps}
	{if $smarty.foreach.opps.iteration <= $results_to_display.opportunities}
	<li><a href="/{$item.type}/view/{$id}" title="{$item.name}"><span class="sprite sprite-opportunity">{$item.name|truncate:30:'...':false}</span></a></li>
	{/if}
	{/foreach}
</ul>
{/if}

{if $activities|@count}
<div><h2>Activities</h2>
<h3><a href="/activities/search/?name={$query|urlencode}*">by Name{if $results_to_display.activities < $activities|@count} ({$results_to_display.activities}/{$activities|@count}){/if}</a></h3></div>
{assign var=act_results value=true}
<ul class="l3">
	{foreach from=$activities item=item key=id name=acts}
	{if $smarty.foreach.acts.iteration <= $results_to_display.activities}
	<li><a href="/{$item.type}/view/{$id}" title="{$item.name}"><span class="sprite sprite-activity">{$item.name|truncate:30:'...':false}</span></a></li>
	{/if}
	{/foreach}
</ul>
{/if}
	
{if !$total_results}
<p class="no_results">No results found</p>
{/if}
