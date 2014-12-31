<ul class="related_list files">
{foreach name=files item=file from=$s3_files}
	<li{if $smarty.foreach.files.last} class="none_yet"{/if}>
		<div class="file_name_size"><a class="view_link sprite sprite-file" title="Download" target="_blank" href="/files/get/{$file->id}">{$file->getFormatted('filename')}</a> ({$file->getFormatted('size')})</div>
		{if $file->canDelete()}
		<form action="/files/delete/" method="post" class="delete_form small">
		<fieldset>
			<input type="hidden" value="{$file->id}" name="id" />
			<button class="button_link delete_link" title="Click to delete">Delete</button>
		</fieldset>
		</form>
		{/if}
		{if $file->comment neq ''}<div class="file_comment">{$file->getFormatted('comment')}</div>{/if}	
		<div class="file_uploaded">Uploaded by {$file->getFormatted('owner')}, {$file->getFormatted('created')}</div>
	</li>
{foreachelse}
	<li class="none_yet">No files</li>
{/foreach}
</ul>
