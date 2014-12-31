<div class="view_page_holder">
	<div id="page_title">
		<img src="/graphics/tactile/icons/notes.png" alt="Note:"/>
		{if $Organisation->id}
		<h1>Editing <span class="highlight">{$OrganisationNote->title}</span></h1>
		{else}
		<h1>New Note</h1>
		{/if}
	</div>
	<form action="/organisations/save_note/" method="post" class="saveform">
		{with model=$OrganisationNote}
		<dl id="view_data_left" class="view_list">
			{input type="text" attribute="title"}
		</dl>
		<dl id="view_data_right" class="view_list">
			{select attribute="organisation_id"}
		</dl>
		{/with}
		<fieldset id="view_data_bottom">
		{textarea attribute="note" model=$OrganisationNote notags=true label="Note Contents"}
		</fieldset>
		<fieldset id="save_container">
		<input type="submit" value="Save" />
		</fieldset>
	</form>
	<div style="clear:both;"></div>
</div>