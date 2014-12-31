<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Select new Opportunity {$option_type}</h2>
		</div>
		<form class="saveform" action="/setup/process_delete" method="post">
			<div class="content_holder">
				<input type="hidden" name="group" value="opportunities" />
				<input type="hidden" name="option" value="{$option_type}" />
				<input type="hidden" name="id" value="{$option_id}" />
				<fieldset>
					<div class="form_help">
						<p><strong>{$opps_count}</strong> of your Opportunities are "{$option_name}", which {$option_type} would you like these changed to?</p>
					</div>
					<div class="content">
						<p>
							<div class="row">
								<label for="change_option">Change {$option_type} to</label>
								<select name="new_option" id="change_option">
									{foreach from=$option_options item=value key=key}
									{if $value ne $option_name}
									<option value="{$key|escape}">{$value|escape}</option>
									{/if}
									{/foreach}
								</select>
							</div>
						</p>
					</div>
				</fieldset>
		    	<fieldset class="save">
		    		<div class="content">
			    		<div class="row">
							<a href="/setup">Cancel</a>
							<span class="or">or</span>
							<input type="submit" class="submit" value="Delete &quot;{$option_name|escape}&quot;" style="width: 180px;" />
						</div>
					</div>
		    	</fieldset>
	    	</div>
		</form>
	</div>
</div>
