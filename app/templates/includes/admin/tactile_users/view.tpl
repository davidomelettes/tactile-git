<script type="text/javascript">
	Tactile.person_id = {$User->person_id|default:'null'};
	Tactile.person = {$User->person|json_encode};
</script>
<div id="right_bar">
	{foldable}
		<p><a href="/users/">Back to User List</a></p>
	{/foldable}
	{foldable key="user_contact_details" title="Contact Details"}
		<ul class="related_list">
			<li class="last">
				<strong><a href="/people/view/{$Person->id}">{$User->person}</a></strong>
				<div>
					<ul class="sidebar_options">
					{if $Person->phone|escape}
						<li class="phone">
							<span class="sprite sprite-phone">{$Person->phone|escape}</span>
						</li>
					{/if}
					{if $Person->email|escape}
						<li class="email">
							<a class="sprite sprite-email">{$Person->email|escape}</a>
						</li>
					{/if}
					</ul>
				</div>
			</li>
		</ul>
	{/foldable}
</div>
<div id="the_page">
	<div id="user_view" class="view_holder">
		<div id="page_title">
			<h2 class="default">
				{if $User->is_enabled()}
				<img id="heading_logo" src="/graphics/tactile/items/users.png" alt="" />
				{else}
				<img id="heading_logo" src="/graphics/tactile/items/users.png" alt="" />
				{/if}
				{$User->getFormatted('username')}
			</h2>
			{include file="elements/edit_delete.tpl" url="users" for="user" model=$User text="User"}
		</div>
		<div class="tag_list">&nbsp;</div>
		<div class="view_nav round-all">
			<h3 id="show_summary_info"{if $view_summary_info} class="selected"{/if}>User Info</h3>
		</div>
		<div class="content_holder" id="summary_info"{if !$view_summary_info} style="display: none;"{/if}>
			<div class="content">
				{include file="elements/summary.tpl model=$User}
			</div>
		</div>
		<div class="content_holder" id="view_nothing_selected"{if $view_summary_info || $view_recent_activity} style="display: none;"{/if}>
			<div class="form_help">
				<p>Expecting something? <span class="sprite sprite-upwards">Use the buttons above</span> to display more.</p>
			</div>
		</div>
	</div>
</div>
