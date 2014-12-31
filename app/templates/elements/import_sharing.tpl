{if $current_user->getModel()->hasFixedPermissions()}
<p>The imported contacts will be {$current_user->getModel()->getDefaultPermissionsString('read')} and {$current_user->getModel()->getDefaultPermissionsString('write')}.</p>
{else}
<p>Unless you want to <a href="#import_sharing" class="show_fields highlight">change it</a>, the imported contacts will be {$current_user->getModel()->getDefaultPermissionsString('read')} and {$current_user->getModel()->getDefaultPermissionsString('write')}.</p>
<div id="import_sharing" style="display: none;">
	<h3>Access Permissions for Imported Contacts</h3>
	<fieldset class="content">
		<div id="read_access">
			<div class="row">
				<h4 class="false_label">Read Access</h4>
				<ul class="radio_options false_input">
					<li>
						<label for="read_everyone">
							<input type="radio" class="checkbox radio" id="read_everyone" name="sharing[read]" value="everyone" {if $sharing_read eq 'everyone' || (!$sharing_read &&$current_user->getModel()->getDefaultPermissions('read') eq 'everyone')} checked="checked"{/if}/>Everyone
						</label>
					</li>
					<li>
						<label for="read_private">
							<input type="radio" class="checkbox radio" id="read_private" name="sharing[read]" value="private" {if $sharing_read eq 'private' || (!$sharing_read && $current_user->getModel()->getDefaultPermissions('read') eq 'private')} checked="checked"{/if}/>Just Me (Private)
						</label>
					</li>
					<li>
						<label for="read_roles">
							<input type="radio" class="checkbox radio" id="read_roles" name="sharing[read]" value="private" {if is_array($sharing_read) || (!$sharing_read && $current_user->getModel()->getDefaultPermissions('read')|is_array)} checked="checked"{/if}/>Select Users...
						</label>
						<select{if !($sharing_read|is_array || ($sharing_read && $current_user->getModel()->getDefaultPermissions('read')|is_array))} style="display: none;" disabled="disabled"{/if} multiple="multiple" id="read_roles_ids" name="sharing[read][]">
							{if is_array($sharing_read)}
							{html_options options=$roles selected=$sharing_read}
							{else}
							{html_options options=$roles selected=$current_user->getModel()->getDefaultPermissions('read')}
							{/if}
						</select>
					</li>
				</ul>
			</div>
		</div>
		<div class="c-left"></div>
		<div id="write_access">
			<div class="row">
				<h4 class="false_label">Read &amp; Write Access</h4>
				<ul class="radio_options false_input">
					<li>
						<label for="write_everyone">
							<input type="radio" class="checkbox radio" id="write_everyone" name="sharing[write]" value="everyone" {if $sharing_write eq 'everyone' || $current_user->getModel()->getDefaultPermissions('write') eq 'everyone'} checked="checked"{/if}/>Everyone
						</label>
					</li>
					<li>
						<label for="write_private">
							<input type="radio" class="checkbox radio" id="write_private" name="sharing[write]" value="private" {if $sharing_write eq 'private' || $current_user->getModel()->getDefaultPermissions('write') eq 'private'} checked="checked"{/if}/>Just Me (Private)
						</label>
					</li>
					<li>
						<label for="write_roles">
							<input type="radio" class="checkbox radio" id="write_roles" name="sharing[write]" value="private" {if is_array($sharing_write) || $current_user->getModel()->getDefaultPermissions('write')|is_array} checked="checked"{/if}/>Select Users...
						</label>
						<select{if !($sharing_write|is_array || ($sharing_write && $current_user->getModel()->getDefaultPermissions('write')|is_array))} style="display: none;" disabled="disabled"{/if} multiple="multiple" id="write_roles_ids" name="sharing[write][]">
							{if is_array($sharing_write)}
							{html_options options=$roles selected=$sharing_write}
							{else}
							{html_options options=$roles selected=$current_user->getModel()->getDefaultPermissions('write')}
							{/if}
						</select>
					</li>
				</ul>
			</div>
		</div>
	</fieldset>
</div>
{/if}
