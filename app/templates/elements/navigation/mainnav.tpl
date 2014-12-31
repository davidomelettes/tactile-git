<ul id="main_nav" class="topNav">
	<li id="search_nav" class="tab fast level1">
		<img src="/graphics/tactile/icons/search.png" alt="Search..." title="Search..." />
		<div class="shadow shadow222">
			<ul class="level2">
				<li class="level2">
					<form action="/search/" method="post"><div><input type="text" id="header_search" class="subtle" name="name" value="Search Everything..." /></div></form>
				</li>
				<li id="advSearch" class="level2"><a class="adv sprite sprite-edit" href="/search/advanced/">Advanced Search</a></li>
			</ul>
		</div>
	</li>
	<li id="add_nav" class="tab fast level1">
		<img src="/graphics/tactile/icons/add.png" alt="Add..." title="Add..." />
		<div class="shadow shadow200">
			<ul class="level2">
				<li class="level2"><a href="/organisations/new/" class="sprite-med sprite-organisation_med">Add New Organisation</a></li>
				<li class="level2"><a href="/people/new/" class="sprite-med sprite-person_med">Add New Person</a></li>
				<li class="level2"><a href="/opportunities/new/" class="sprite-med sprite-opportunity_med">Add New Opportunity</a></li>
				<li class="level2"><a href="/activities/new/" class="sprite-med sprite-activity_med">Add New Activity</a></li>
			</ul>
		</div>
	</li>
	<li class="slow level1{if $_area eq ''} current{if $highlight} highlight_{$highlight}{/if}{/if}" id="current_app">
		<a href="/" class="tab with_menu">My Dashboard</a><a href="/" class="tab menu">&nbsp;</a>
		<div class="shadow shadow270">
			<ul class="level2">
				<li class="level2"><a href="/?view=notes_emails">Notes &amp; Emails</a></li>
				<li class="level2"><a href="/?view=notes_emails_acts">Notes, Emails, &amp; Completed Activities</a></li>
				<li class="level2"><a href="/?view=custom">Recent Activity (Custom)</a></li>
			</ul>
		</div>
	</li>
	<li class="slow level1{if $_area eq 'organisations'} current{if $highlight} highlight_{$highlight}{/if}{/if}">
		<a href="/organisations/" class="tab with_menu">Organisations</a><a href="/organisations/" class="tab menu">&nbsp;</a>
		<div class="shadow shadow200">
			<ul class="level2">
				<li class="level2"><a href="/organisations/alphabetical/">All Organisations (A-Z)</a></li>
				<li class="level2"><a href="/organisations/recently_viewed/">Recently Viewed Organisations</a></li>
				<li class="level2"><a href="/organisations/recent/">Recently Added Organisations</a></li>
				<li class="level2"><a href="/organisations/mine/">Organisations Assigned to Me</a></li>
				{foreach from=$advanced_searches_org item=search name=tab}
				{if $smarty.foreach.tab.iteration < 5}<li class="level2 advSearch"><a href="/search/recall/{$search->id}">{$search->name|escape}</a></li>{/if}
				{/foreach}
			</ul>
		</div>
	</li>
	<li class="slow level1{if $_area eq 'people'} current{if $highlight} highlight_{$highlight}{/if}{/if}">
		<a href="/people/" class="tab with_menu">People</a><a href="/people/" class="tab menu">&nbsp;</a>
		<div class="shadow shadow200">
			<ul class="level2">
				<li class="level2"><a href="/people/firstname/">All People (by Firstname A-Z)</a></li>
				<li class="level2"><a href="/people/alphabetical/">All People (by Surname A-Z)</a></li>
				<li class="level2"><a href="/people/recently_viewed/">Recently Viewed People</a></li>
				<li class="level2"><a href="/people/recent/">Recently Added People</a></li>
				<li class="level2"><a href="/people/mine/">People Assigned to Me</a></li>
				<li class="level2"><a href="/people/individuals/" title="People not in an Organisation">Individuals</a></li>
				{foreach from=$advanced_searches_per item=search name=tab}
				{if $smarty.foreach.tab.iteration < 5}<li class="level2 advSearch"><a href="/search/recall/{$search->id}">{$search->name|escape}</a></li>{/if}
				{/foreach}
			</ul>
		</div>
	</li>
	<li class="slow level1{if $_area eq 'opportunities'} current{if $highlight} highlight_{$highlight}{/if}{/if}">
		<a href="/opportunities/" class="tab with_menu">Opportunities</a><a href="/opportunities/" class="tab menu">&nbsp;</a>
		<div class="shadow shadow200">
			<ul class="level2">
				<li class="level2"><a href="/opportunities/open/">All Open Opportunities (A-Z)</a></li>
				<li class="level2"><a href="/opportunities/open_date/">All Open Opportunities (Date)</a></li>
				<li class="level2"><a href="/opportunities/mine_open/">My Open Opportunities (A-Z)</a></li>
				<li class="level2"><a href="/opportunities/mine_open_date/">My Open Opportunities (Date)</a></li>
				<li class="level2"><a href="/opportunities/recently_viewed/">Recently Viewed Opportunities</a></li>
				<li class="level2"><a href="/opportunities/most_recent/">Recently Added Opportunities</a></li>
				<li class="level2"><a href="/opportunities/recently_won/">Recently Won Opportunities</a></li>
				<li class="level2"><a href="/opportunities/recently_lost/">Recently Lost Opportunities</a></li>
				<li class="level2"><a href="/opportunities/archived/">Archived Opportunities</a></li>
				<li class="level2"><a href="/opportunities/mine/">Opportunities Assigned to Me</a></li>
				{foreach from=$advanced_searches_opp item=search name=tab}
				{if $smarty.foreach.tab.iteration < 5}<li class="level2 advSearch"><a href="/search/recall/{$search->id}">{$search->name|escape}</a></li>{/if}
				{/foreach}
			</ul>
		</div>
	</li>
	<li class="slow level1{if $_area eq 'activities'} current{if $highlight} highlight_{$highlight}{/if}{/if}">
		<a href="/activities/" class="tab with_menu">Activities</a><a href="/activities/" class="tab menu">&nbsp;</a>
		<div class="shadow shadow200">
			<ul class="level2">
				<li class="level2"><a href="/activities/all/">All Activities (A-Z)</a></li>
				<li class="level2"><a href="/activities/recently_viewed/">Recently Viewed Activities</a></li>
				<li class="level2"><a href="/activities/all_current/">All Current Activities (by Date)</a></li>
				<li class="level2"><a href="/activities/mine/">Activities Assigned to Me</a></li>
				<li class="level2"><a href="/activities/my_overdue/">My Overdue Activities</a></li>
				<li class="level2"><a href="/activities/all_overdue/">All Overdue Activities</a></li>
				<li class="level2"><a href="/activities/recently_completed">Recently Completed Activities</a></li>
				{foreach from=$advanced_searches_act item=search name=tab}
				{if $smarty.foreach.tab.iteration < 5}<li class="level2 advSearch"><a href="/search/recall/{$search->id}">{$search->name|escape}</a></li>{/if}
				{/foreach}
			</ul>
		</div>
	</li>
</ul>