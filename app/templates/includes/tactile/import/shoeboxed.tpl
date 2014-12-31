<div id="right_bar">
	{foldable}
		<p><a href="/import/">Select a different Import type</a></p>
	{/foldable}
	{foldable extra_class=help key=shoeboxed_help title="What is Shoeboxed?"}
	<p><a href="http://www.shoeboxed.com">Shoeboxed</a> is an online service that scans receipts and business cards for you,
	turning them into contact data and expense reports.</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
        <div id="page_title">
        	<h2>Import Organisations and People into Tactile</h2>
        </div>
        <form action="/import/upload/" method="post" class="saveform" enctype="multipart/form-data">
        	<input type="hidden" name="file_type" value="shoeboxed">
			<div class="content_holder">
				<fieldset>
					<h3>Shoeboxed Contacts</h3>
					<div class="form_help">
						<p>Pressing "Import" below will take you to the Shoeboxed website,
						which will ask you to log in and allow Tactile temporary access to your data.</p>
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
