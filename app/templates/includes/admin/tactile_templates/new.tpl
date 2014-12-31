<div id="right_bar">
	{foldable}
		<p><a href="/setup/email/">Back to TactileMail Setup</a></p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			{if $EmailTemplate->id}
			<h2>Editing {$EmailTemplate->getFormatted('username')}</h2>
			{else}
			<h2>New Template</h2>
			{/if}
		</div>
		<form action="/templates/save/" method="post" class="saveform">
			<div class="content_holder">
				<fieldset>
					<h3>Template Details</h3>
					<div class="form_help">
						<p>Yo yo yo.</p> 
					</div>
					<div class="content">
						{with model=$EmailTemplate tags="none"}
						{input type="hidden" attribute="id"}
						{input type="checkbox" attribute="enabled"}
						{input type="text" attribute="name"}
						{input type="text" attribute="subject"}
						{/with}
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset>
					<h3>Template Body</h3>
					<div class="content">
						<textarea id="EmailTemplate_description" name="EmailTemplate[body]" cols="20" rows="4">{$EmailTemplate->body}</textarea>
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<fieldset id="save_container">
					<input type="submit" value="Save" />
				</fieldset>
			</div>
		</form>
	</div>
</div>
