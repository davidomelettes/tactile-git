{if $flash->hasErrors()}
	{flash}
{else}
<div id="normal_upload">
	<form action="/{$url_part}/save_file/{$model->id}" method="post" enctype="multipart/form-data">
		<fieldset class="sidebar">
			<input type="hidden" name="MAX_FILE_SIZE" value="{$form_max_filesize}" />
			<p>Use the button below to choose a file, add a comment if you wish, and then click 'Upload'.</p>
			<p>Files can be up to {'upload_max_filesize'|ini_get} in size.</p>
			<p><input class="file" type="file" name="Filedata" id="file_upload" /></p>
			<p><label for="file_upload_comment" id="file_upload_comment_label">Add a comment (optional)</label><br /><input type="text" name="comment" id="file_upload_comment" />
			<input type="submit" value="Upload" id="file_upload_button"/></p>
		</fieldset>
	</form>
	<p>You are using {$usage} of your {$allowance} filespace allowance.</p>
</div>
{/if}