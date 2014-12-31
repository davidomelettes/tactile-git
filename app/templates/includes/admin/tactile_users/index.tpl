<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable key="user_usage" title="User Usage"}
		<p>You are currently using <strong>{$users_used}</strong> of your <strong>{$users_limit}</strong> User limit</p>
		{if $plan->is_per_user()}
		<p><a href="/users/purchase/">Increase Limit</a></p>
		{else}
		<p><a href="/account/change_plan/">Increase Limit</a></p>
		{/if}
	{/foldable}
	{foldable key="users_overview_help" title="Users Help" extra_class="help"}
		<p>In Tactile, Users are <a href="/people/">People</a> who can log in with their username and password.</p>
		<p>Users can be placed in multiple <a href="/groups/">Groups</a>.</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/paging.tpl" for="users" add="user" add_text="Add New User"}
			<h2>Users</h2>
		</div>
		<div id="page_main">
			<table id="user_index" class="index_table">
				<thead>
					<tr>
						<th class="primary">Username</th>
						<th>Person</th>
						<th>Enabled?</th>
						<th>Admin?</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th class="primary">Username</th>
						<th>Person</th>
						<th>Enabled?</th>
						<th>Admin?</th>
					</tr>
				</tfoot>
				<tbody>
					{foreach name=users item=user from=$users}
					<tr>
						<td class="primary">
							<a href="/users/view/{$user->username|urlencode}/" class="sprite-med sprite-user{if !$user->is_enabled()}_disabled{/if}_med">{$user->getFormatted('username')}</a>
						</td>
						<td><a href="/people/view/?id={$user->person_id}">{$user->person|escape}</a></td>
						<td>{$user->getFormatted('enabled')}</td>
						<td>{$user->getFormatted('is_admin')}</td>
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
