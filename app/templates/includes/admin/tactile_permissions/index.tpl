<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable title="Permissions Help" key="permissions_help" extra_class="help"}
		<p>Control the features your users have access to, define the default permission settings when creating new Organisations, and whether they can be changed.</p>
	{/foldable}
</div>
<div id="the_page">
	<script type="text/javascript">
		Tactile.default_permissions = {literal}{{/literal}{strip}
		{foreach from=$users item=user name=default_permissions}
			"{$user->username|escape}":{literal}{{/literal}"fixed":{$user->hasFixedPermissions()|json_encode},"permissions_read":{$user->getDefaultPermissions('read')|@json_encode},"permissions_write":{$user->getDefaultPermissions('write')|@json_encode}{literal}}{/literal}
			{if !$smarty.foreach.default_permissions.last},{/if}
		{/foreach}
		{literal}}{/literal}{/strip};
	</script>
	<div class="edit_holder">
		<div id="page_title">
			<h2>User Permissions</h2>
		</div>
       <div id="pref_nav">
			<ul>
				<li{if $pref_view eq 'feature_control'} class="on"{/if}><a href="/permissions/feature_control/" id="pref_tab_feature_control">Enabled Features</a></li>
				<li class="arrow {if $pref_view eq 'feature_control'} on_left{elseif $pref_view eq 'default_permissions'} on_right{/if}">&nbsp;</li>
				<li{if $pref_view eq 'default_permissions'} class="on"{/if}><a href="/permissions/default_permissions/" id="pref_tab_default_permissions">Default Permissions</a></li>
				<li class="arrow {if $pref_view eq 'default_permissions'} on_left{/if}">&nbsp;</li>
			</ul>
		</div>
		
		<div id="pref_content">
			
			<div class="content_holder">
				<div id="pref_feature_control" class="show"{if $pref_view neq 'feature_control'} style="display: none;"{/if}>
					<form action="/permissions/save/" method="post" class="saveform">
						<fieldset>
							<div class="form_help">
								<p>The following options will control whether non-admin users on your account can access certain features.</p>
							</div>
							<div class="content">
								<div class="row">
									<label for="permission_import_enabled">Enable Contact Import?</label>
									<input class="checkbox" type="checkbox" id="permission_import_enabled" name="permission_import_enabled"{if $permission_import_enabled} checked="checked"{/if} />
								</div>
								<div class="row">
									<label for="permission_export_enabled">Enable Contact Export?</label>
									<input class="checkbox" type="checkbox" id="permission_export_enabled" name="permission_export_enabled"{if $permission_export_enabled} checked="checked"{/if} />
								</div>
							</div>
						</fieldset>
						<fieldset id="save_container" class="prefs_save">
							<div class="content">
								<div class="row">
									<input type="submit" value="Save" />
								</div>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
			
			<div class="content_holder">
				<div id="pref_default_permissions" class="show"{if $pref_view neq 'default_permissions'} style="display: none;"{/if}>
					<div class="form_help">
						<p>Set the default permissions for each User, when creating a new Organisation.</p>
						<p>A User with 'fixed' permissions can not change them.</p>
					</div>
					<div class="content">
						<table id="user_index" class="index_table">
							<thead>
								<tr>
									<th>User</th>
									<th>Admin?</th>
									<th>Default Read Permissions</th>
									<th>Default Write Permissions</th>
									<th>Permissions Fixed?</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								{foreach from=$users item=user}
								<tr>
									<td class="primary"><span class="sprite-med sprite-user_med">{$user->username}</span></td>
									<td>{$user->getFormatted('is_admin')}</td>
									<td>{$user->getDefaultPermissionsString('read', true)}</td>
									<td>{$user->getDefaultPermissionsString('write', true)}</td>
									<td>{if $user->hasFixedPermissions()}<img alt="t" src="/graphics/tactile/true.png">{else}<img alt="f" src="/graphics/tactile/false.png">{/if}</td>
									<td><a class="action">Change</a></td>
								</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			</div>
			
		</div>
	</div>
</div>
