
<div id="the_page">
	<div class="edit_holder">
        <div id="page_title">
        	<h2>Highrise Import</h2>
        </div>
        <form action="/import/highrise_users/" method="post" class="saveform" >
			<div class="content_holder">
				<fieldset>
					<h3>Match Highrise Users to Tactile Users</h3>
					<div class="content">
					{foreach from=$highrise_users item=hr}
						<div class="row">
							<label for="user[{$hr->id}]">{$hr->name}</label> 
							<select name="user[{$hr->id}]">
								{foreach from=$tactile_users key=k item=user}
									<option value="{$k}" {if $user eq $hr->name}selected{/if}>{$user}</option>	
								{/foreach}
							</select>
						</div>
					{/foreach}
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<fieldset>
					<h3>Match Opportunity Types</h3>
					<div class="content">
						<div class="row">
							<label for="types['won']">Won</label> 
							<select name="types['won']">
								{foreach from=$types key=k item=name}
									<option value="{$k}" {if $name eq "Won"}selected{/if}>{$name}</option>	
								{/foreach}
							</select>
						</div>
						<div class="row">
							<label for="types['lost']">Lost</label> 
							<select name="types['lost']">
								{foreach from=$types key=k item=name}
									<option value="{$k}" {if $name eq "Lost"}selected{/if}>{$name}</option>	
								{/foreach}
							</select>
						</div>		
						<div class="row">						
							<label for="types['pending']">Pending</label> 
							<select name="types['pending']">
								{foreach from=$types key=k item=name}
									<option value="{$k}">{$name}</option>	
								{/foreach}
							</select>
						</div>
					</div>
				</fieldset>
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
