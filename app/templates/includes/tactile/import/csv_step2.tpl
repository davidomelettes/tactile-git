<div id="right_bar">
	{foldable}
		<p><a href="/import/">Select a different Import type</a></p>
	{/foldable}
	{foldable extra_class="help" title="CSV Import"}
		<p>Tactile CRM can create Organisations and People from your CSV upload,
		but we'll need a hand matching up our fields to the column headings we've found in your file.</p>
	{/foldable}
	{foldable title="Assigned Headings" key="csv_imported_headings" extra_class="unfoldable"}
		<p>For each field in Tactile you want populated,
		choose an option from the list of headings in the corresponding drop-down menu.</p>
		<ul class="sidebar_options">
			{foreach from=$select_options item=field key=value}
			{if $value !== ''}
			<li class="{if $value|in_array:$selected_fields}yes{else}no{/if}" id="csv_heading_{$value}"><span class="sprite">{$field}</span></li>
			{/if}
			{/foreach}
		</ul>
		<p>(<span class="no sprite"> = Unassigned</span>, <span class="yes sprite"> = Assigned</span>)</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
        <div id="page_title">
        	<h2>Import Organisations and People into Tactile CRM</h2>
        </div>
        <form action="/import/upload/" method="post" class="saveform" enctype="multipart/form-data">
        	<input type="hidden" name="file_type" value="csv" />
        	<div class="content_holder">
				<fieldset>
					<h3>CSV File Import</h3>
					<div class="content" id="csv_import_assignment">
						<div class="col-2">
							<div class="left round-all">
								<h4 class="sprite sprite-organisation">Organisation</h4>
								{assign var=index value="Organisation"}
								{foreach from=$tactile_organisation_fields.$index key=label item=field}
								<div class="row">
									<label for="assign_{$field}">{$label}</label>
									<select id="assign_{$field}" name="CSVField[{$field}]">
									{foreach from=$select_options item=option key=value}
									<option value="{$value}"{if $selected_fields.$field === $value} selected="selected"{/if}>{$option}</option>
									{/foreach}
									</select>
								</div>
								{/foreach}
							</div>
							<div class="left round-all">
								<h4 class="sprite sprite-person">Person</h4>
								{foreach from=$tactile_person_fields.Person key=label item=field}
								<div class="row">
									<label for="assign_{$field}">{$label}</label>
									<select id="assign_{$field}" name="CSVField[{$field}]">
									{foreach from=$select_options item=option key=value}
									<option value="{$value}"{if $selected_fields.$field === $value} selected="selected"{/if}>{$option}</option>
									{/foreach}
									</select>
								</div>
								{/foreach}
							</div>
						</div>
						
						<div class="col-2" id="csv_assign_contacts">
							<div class="left round-all">
								<h4 class="sprite sprite-organisation">Organisation Contact Methods</h4>
								{assign var=index value="Organisation Contact Methods"}
								{foreach from=$tactile_organisation_fields.$index key=label item=field}
								<div class="row">
									<label for="assign_{$field}">{$label}</label>
									<select id="assign_{$field}" name="CSVField[{$field}]">
									{foreach from=$select_options item=option key=value}
									<option value="{$value}"{if $selected_fields.$field === $value} selected="selected"{/if}>{$option}</option>
									{/foreach}
									</select>
								</div>
								{/foreach}
							</div>
							<div class="left round-all">
								<h4 class="sprite sprite-person">Person Contact Methods</h4>
								{assign var=index value="Person Contact Methods"}
								{foreach from=$tactile_person_fields.$index key=label item=field}
								<div class="row">
									<label for="assign_{$field}">{$label}</label>
									<select id="assign_{$field}" name="CSVField[{$field}]">
									{foreach from=$select_options item=option key=value}
									<option value="{$value}"{if $selected_fields.$field === $value} selected="selected"{/if}>{$option}</option>
									{/foreach}
									</select>
								</div>
								{/foreach}
							</div>
						</div>
						
						<div class="col-2" id="csv_assign_addresses">
							<div class="left round-all">
								<h4 class="sprite sprite-organisation">Organisation Address</h4>
								{assign var=index value="Organisation Address"}
								{foreach from=$tactile_organisation_fields.$index key=label item=field}
								<div class="row">
									<label for="assign_{$field}">{$label}</label>
									<select id="assign_{$field}" name="CSVField[{$field}]">
									{foreach from=$select_options item=option key=value}
									<option value="{$value}"{if $selected_fields.$field === $value} selected="selected"{/if}>{$option}</option>
									{/foreach}
									</select>
								</div>
								{/foreach}
							</div>
							<div class="left round-all">
								<h4 class="sprite sprite-person">Person Address</h4>
								{assign var=index value="Person Address"}
								{foreach from=$tactile_person_fields.$index key=label item=field}
								<div class="row">
									<label for="assign_{$field}">{$label}</label>
									<select id="assign_{$field}" name="CSVField[{$field}]">
									{foreach from=$select_options item=option key=value}
									<option value="{$value}"{if $selected_fields.$field === $value} selected="selected"{/if}>{$option}</option>
									{/foreach}
									</select>
								</div>
								{/foreach}
							</div>
						</div>
						
						{if $organisation_custom_fields|@count || $person_custom_fields|@count}
						<div class="col-2" id="csv_assign_custom">
							<div class="left">
								{if $organisation_custom_fields|@count}
								<h4 class="sprite sprite-organisation">Organisation Custom Fields</h4>
								{foreach from=$organisation_custom_fields item=field}
								<div class="row">
									<label for="assign_custom_organisation_{$field->id}">{$field->name|escape}</label>
									<select id="assign_custom_organisation_{$field->id}" name="CSVField[organisationcustom_{$field->id}][index]">
									{foreach from=$select_options item=option key=value}
									<option value="{$value}">{$option}</option>
									{/foreach}
									</select>
								</div>
								{if $field->type eq 's' && $current_user->isAdmin()}
								<div class="row">
									<label for="assign_custom_organisation_{$field->id}_autocreate">Auto-create options?</label>
									<input type="checkbox" class="checkbox" id="assign_custom_organisation_{$field->id}_autocreate" name="CSVField[organisationcustom_{$field->id}][autocreate]" />
								</div>
								{/if}
								{/foreach}
								{/if}
							</div>
							<div class="left">
								{if $person_custom_fields|@count}
								<h4 class="sprite sprite-person">Person Custom Fields</h4>
								{foreach from=$person_custom_fields item=field}
								<div class="row">
									<label for="assign_custom_person_{$field->id}">{$field->name|escape}</label>
									<select id="assign_custom_person_{$field->id}" name="CSVField[personcustom_{$field->id}][index]">
									{foreach from=$select_options item=option key=value}
									<option value="{$value}">{$option}</option>
									{/foreach}
									</select>
								</div>
								{if $field->type eq 's' && $current_user->isAdmin()}
								<div class="row">
									<label for="assign_custom_person_{$field->id}_autocreate">Auto-create options?</label>
									<input type="checkbox" class="checkbox" id="assign_custom_person_{$field->id}_autocreate" name="CSVField[personcustom_{$field->id}][autocreate]" />
								</div>
								{/if}
								{/foreach}
								{/if}
							</div>
						</div>
						{/if}
						
						<div class="clear" />
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				{include file="elements/import_sharing.tpl}
			</div>
			
			<div class="content_holder">
				<fieldset id="import_tagging">
					<h3>Tag the new Contacts</h3>
					<div class="form_help">
						<p>Use this field to tag all the imported contacts. Specify multiple tags by separating each with a comma (,).</p>
						<p>Your imported records will automatically be tagged with "<strong>{$suggested_tag}</strong>".</p>
					</div>
					<div class="content">
						<div class="row">
							<label class="tag_list" for="tags">Tags</label>
							<input name="tags" class="tag_list" id="tags" type="text" value="{if $tags != ''}{$tags}{/if}" />
						</div>
					</div> 
				</fieldset>
			</div>
			
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Import" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>
