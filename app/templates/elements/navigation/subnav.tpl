<ul id="sub_nav" class="topNav">
	{welcome_message}
	<li class="level1{if $_area eq 'tags'} current{if $highlight} highlight_{$highlight}{/if}{/if}">
		<a href="/tags/" class="tab">Tags</a>
	</li>
	<li class="slow level1{if $_area eq 'emails'} current{if $highlight} highlight_{$highlight}{/if}{/if}">
		<a href="/emails/" class="tab with_menu">Email</a><a href="/emails/" class="tab menu">&nbsp;</a>
		<div class="shadow shadow200">
			<ul class="level2">
				<li class="level2"><a href="/emails/all/">All Emails</a></li>
				<li class="level2"><a href="/emails/unassigned/">Unassigned Emails</a></li>
				<li class="level2"><a href="/emails/incoming/">Incoming Emails</a></li>
				<li class="level2"><a href="/emails/outgoing/">Outgoing Emails</a></li>
			</ul>
		</div>
	</li>
	<li class="fast level1{if $_area eq 'graphs'} current{if $highlight} highlight_{$highlight}{/if}{/if}" id="reports_nav">
		<a href="/graphs/" class="tab">Reports</a>
		<div class="shadow shadow430">
			<div class="reports">
				<ul id="reports_list" class="level2">
					<li class="level2 subtitle"><strong>Reports</strong></li>
					<li class="level2"><a href="/graphs/pipeline_report">Pipeline Report</a></li>
					<li class="level2"><a href="/graphs/sales_report">Sales Report</a></li>
					<li class="level2 subtitle">Want More? <a href="http://feedback.omelett.es/pages/tactile_crm">Let us know</a>.</li>
				</ul>
				<ul id="graphs_list" class="level2">
					<li class="level2 subtitle"><strong>Graphs</strong></li>
					<li class="level2"><a href="/graphs/sales_history">Sales History</a></li>
					<li class="level2"><a href="/graphs/pipeline">Pipeline</a></li>
					<li class="level2 subtitle"><strong>Opportunities</strong></li>
					<li class="level2"><a href="/graphs/opps_by_source_qty">by Source (Quantity)</a></li>
					<li class="level2"><a href="/graphs/opps_by_source_cost">by Source (Cost)</a></li>
					<li class="level2"><a href="/graphs/opps_by_type_qty">by Type (Quantity)</a></li>
					<li class="level2"><a href="/graphs/opps_by_type_cost">by Type (Cost)</a></li>
					<li class="level2"><a href="/graphs/opps_by_status_qty">by Status (Quantity)</a></li>
				</ul>
				<div style="clear: both;"></div>
			</div>
		</div>
	</li>
</ul>