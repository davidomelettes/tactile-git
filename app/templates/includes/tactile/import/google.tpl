<div id="right_bar">
	{foldable}
		<p><a href="/import/">Select a different Import type</a></p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
        <div id="page_title">
        	<h2>Import Organisations and People into Tactile</h2>
        </div>
        {if !$groups}
        <form action="/import/google/" method="post" class="saveform" enctype="multipart/form-data">
        	<input type="hidden" name="file_type" value="gdata">
			<div class="content_holder">
				<fieldset>
					<h3>Google Contacts</h3>
					<div class="form_help">
						{if !$google_token}
						<p class="large_form_text">To import from your GMail address book, you first need to authenticate with Google and <a class="action" href="{$google_auth_url|escape}">allow Tactile access to your account</a>.</p>
						{else}
						<p class="large_form_text">We don't ask for your password as you've told Google to allow Tactile access to your data; however, we need the username to know which Address Book to ask for, so make sure you enter your full username, e.g. <em class="with_colour">example@gmail.com</em> and not just example,  and we will import your contacts right away.</p>
						{/if}
					</div>
					<div class="content">
						<div class="row">
							<div id="gmail_username">
								<label for="gdata_username">Gmail Username</label>
								<input {if !$google_token}disabled="disabled" {/if} type="text" name="gdata_username" id="gdata_username"/>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Next" />
						</div>
					</div>
				</fieldset>
			</div>
			{/if}
			{if $groups}
			<form action="/import/upload/" method="post" class="saveform" enctype="multipart/form-data">
			<input type="hidden" name="file_type" value="gdata">
			<input type="hidden" name="gdata_username" value="{$gdata_username}">
			<div class="content_holder">
				<fieldset>
					<h3>Select Group to Import</h3>
					<div class="content">
						<div class="row">
							<div id="gmail_username">
								<label for="gdata_group">Contacts Group</label>
								<select name="gdata_group">
								{foreach from=$groups key=k item=name}
									<option value="{$k}">{$name}</option>
								{/foreach}
								</select>
							</div>
						</div>		
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
			{/if}
		</form>
	</div>
</div>
