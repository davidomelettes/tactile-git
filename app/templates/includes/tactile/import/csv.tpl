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
        <form action="/import/csv/" method="post" class="saveform" enctype="multipart/form-data">
        	<input type="hidden" name="file_type" value="csv">
        	<div class="content_holder">
				<fieldset>
					<h3>CSV File Import</h3>
					<div class="form_help">
						<p>Please select a file to upload. For advice about exporting your CSV file for Tactile, please see <a href="http://www.tactilecrm.com/help">our help pages</a>.</p>
					</div>
					<div class="content">
						<div id="upload_file">
							<div class="row">
								<label for="import_file">Upload File</label>
								<input class="file" type="file" name="upload_file" id="import_file" />
							</div>
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
