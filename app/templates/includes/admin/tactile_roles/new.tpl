<div id="right_bar">
	{foldable}
		<p><a href="/groups/">Back to Group List</a></p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			{if $Role->id}
			<h2>Editing {$Role->getFormatted('name')}</h2>
			{else}
			<h2>New Group</h2>
			{/if}
		</div>
		<form action="/groups/save/" method="post" class="saveform">
			{with model=$Role}
			<div class="content_holder">
				<fieldset>
					<h3>Group Details</h3>
					<div class="form_help">
						<p>Name is a required field.</p>
					</div>
					<div class="content">
						{input type="hidden" attribute="id"}
						{input type="text" attribute="name"}
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset>
					<h3>Description</h3>
					<div class="content">
						<div class="row">
							<textarea id="Role_description" name="Role[description]">{$Role->description}</textarea>
						</div>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset id="roles_add_users">
					<h3>Which Users</h3>
					<div class="form_help">
						<p>Use Ctrl-click to select multiple Users.</p>
					</div>
					<div class="content">
						<div class="row">
							<label for="Role_users">Usernames</label>
							<select multiple="multiple" id="Role_users" name="Role[users][]">
								{html_options options=$all_users selected=$selected_users}
							</select>
						</div>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Save" />
						</div>
					</div>
				</fieldset>
			</div>
		{/with}
		</form>
	</div>
</div>
