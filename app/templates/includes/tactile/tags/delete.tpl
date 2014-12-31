<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Do you want to delete &ldquo;{$tag->getFormatted('name')}&rdquo;?</h2>
		</div>
		<form class="saveform delete_form" action="/tags/delete" method="post" id="tag_action_form">
			<div class="content_holder">
				<fieldset>
					<div class="form_help">
						<p>Deleting the tag will remove it from all objects (people, organisations etc.) but the objects themselves will not be deleted.</p>
					</div>
					<div class="content">
						<input type="hidden" value="{$tag->name|escape}" name="tag" />
						<div class="row">
							<span class="false_label">Tag</span>
							<span class="false_input">{$tag->getFormatted('name')}</span><br />
						</div>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset class="submit cancel_or_save" id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Delete" />
							<span class="or"> or </span>
							<a class="action" href="/tags/by_tag?tag={$tag->name|urlencode}">Back to Tagged Items</a>
						</div>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>