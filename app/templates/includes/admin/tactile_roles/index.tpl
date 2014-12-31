<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable key="roles_overview_help" title="Group Help" extra_class="help"}
		<p>In Tactile, Groups are collections of <a href="/users/">Users</a>.</p>
		<p>Groups can be referred to when specifying the access permissions of <a href="/organisations/">Organisations</a>.</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/paging.tpl" for="groups" add="group" add_text="Add New Group"}
			<h2>Groups</h2>
		</div>
		<div id="page_main">
			<table id="group_index" class="index_table">
				<thead>
					<tr>
						<th class="primary">Name</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th class="primary">Name</th>
					</tr>
				</tfoot>
				<tbody>
					{foreach name=roles item=role from=$roles}
					<tr>
						<td class="group_name primary"><a href="/groups/edit/{$role->id}/">{$role->getFormatted('name')}</a></td>
					</tr>
					{foreachelse}
					<tr>
						<td>No groups to show</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			{if $num_pages > 1}
			<div class="bottom_paging">
				{include file="elements/paging.tpl" for="activities" add="activity" add_text="Add New Activity"}
			</div>
			{/if}
		</div>
	</div>
</div>
