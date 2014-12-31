{if !$Organisation->id || $Organisation->owner eq $current_user->getRawUsername() || isModuleAdmin()}

{if $current_user->getModel()->hasFixedPermissions()}
{if !$Organisation->id}
<p>This Organisation will be {$current_user->getModel()->getDefaultPermissionsString('read')} and {$current_user->getModel()->getDefaultPermissionsString('write')}.</p>
{else}
<p>This Organisation is Viewable {if $Organisation->getReadString() eq $Organisation->getWriteString()} and editable {/if}<strong>{$Organisation->getReadString()}</strong>{if $Organisation->getReadString() neq $Organisation->getWriteString()} and Editable <strong>{$Organisation->getWriteString()}</strong>{/if}.</p>
{/if}
{else}
{if !$Organisation->id}
<p>Unless you want to <a href="#client_sharing" class="show_fields highlight">change it</a>, this Organisation will be {$current_user->getModel()->getDefaultPermissionsString('read')} and {$current_user->getModel()->getDefaultPermissionsString('write')}.</p>
{else}
<p>This Organisation is Viewable {if $Organisation->getReadString() eq $Organisation->getWriteString()} and editable {/if}<strong>{$Organisation->getReadString()}</strong>{if $Organisation->getReadString() neq $Organisation->getWriteString()} and Editable <strong>{$Organisation->getWriteString()}</strong>{/if}, but you can  <a href="#client_sharing" class="show_fields highlight">change it</a>.</p>
{/if}
{/if}

<fieldset id="client_sharing" style="display:none;">
	<h3>Access Permissions</h3>
	<div class="form_help">
		<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
		<p>This Organisation's owner {if $Organisation->owner eq $current_user->getRawUsername()}(you){else}{/if}
		can always see and edit it.</p>
		<p>Users with write access can edit and delete this Organisation.</p>
	</div>
	<div class="content">
		<div id="read_access">
			<div class="row">
				<h4 class="false_label">Read Access</h4>
				<ul class="radio_options false_input">
					<li>
						<label for="read_everyone">
							<input type="radio" class="radio checkbox" id="read_everyone" name="Sharing[read]" value="everyone"{if $Organisation->getRead() eq 'everyone' || (!$Organisation->id && $current_user->getModel()->getDefaultPermissions('read') eq 'everyone')} checked="checked"{/if} />Everyone
						</label>
					</li>
					<li>
						<label for="read_private">
							<input type="radio" class="radio checkbox" id="read_private" name="Sharing[read]" value="private"{if $Organisation->getRead() eq 'private' ||  $current_user->getModel()->getDefaultPermissions('read') eq 'private'} checked="checked"{/if} />Just Me (Private)
						</label>
					</li>
					<li>
						<label for="read_roles">
							<input type="radio" class="radio checkbox" id="read_roles" name="Sharing[read]" value="private"{if ($Organisation->getRead() eq 'multi' && $Organisation->id) || $current_user->getModel()->getDefaultPermissions('read')|is_array} checked="checked"{/if} />Select Users...
						</label>
						<select{if (!$Organisation->id && !$current_user->getModel()->getDefaultPermissions('read')|is_array) || $Organisation->getRead() neq 'multi'} style="display: none;" disabled="disabled"{/if} multiple="multiple" id="read_roles_ids" name="Sharing[read][]">
							{if !$Organisation->id && $current_user->getModel()->getDefaultPermissions('read')|is_array}
							{html_options options=$roles selected=$current_user->getModel()->getDefaultPermissions('read')}
							{else}
							{html_options options=$roles selected=$Organisation->getReadRoles()}
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
							<input type="radio" class="radio checkbox" id="write_everyone" name="Sharing[write]" value="everyone"{if $Organisation->getWrite() eq 'everyone' || (!$Organisation->id && $current_user->getModel()->getDefaultPermissions('write') eq 'everyone')} checked="checked"{/if} />Everyone
						</label>
					</li>
					<li>
						<label for="write_private">
							<input type="radio" class="radio checkbox" id="write_private" name="Sharing[write]" value="private"{if $Organisation->getWrite() eq 'private' ||  $current_user->getModel()->getDefaultPermissions('write') eq 'private'} checked="checked"{/if} />Just Me (Private)
						</label>
					</li>
					<li>
						<label for="write_roles">
							<input type="radio" class="radio checkbox" id="write_roles" name="Sharing[write]" value="private"{if ($Organisation->getWrite() eq 'multi' && $Organisation->id) || $current_user->getModel()->getDefaultPermissions('write')|is_array} checked="checked"{/if} />Select Users...
						</label>
						<select{if (!$Organisation->id && !$current_user->getModel()->getDefaultPermissions('write')|is_array) || $Organisation->getWrite() neq 'multi'} style="display: none;" disabled="disabled"{/if} multiple="multiple" id="write_roles_ids" name="Sharing[write][]">
							{if !$Organisation->id && $current_user->getModel()->getDefaultPermissions('write')|is_array}
							{html_options options=$roles selected=$current_user->getModel()->getDefaultPermissions('write')}
							{else}
							{html_options options=$roles selected=$Organisation->getWriteRoles()}
							{/if}
						</select>
					</li>
				</ul>
			</div>
		</div>
	</div>
</fieldset>

{/if}
