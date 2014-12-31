<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Do you want to rename &ldquo;{$old_tag|escape}&rdquo;?</h2>
		</div>
		<form class="saveform" action="/tags/dorename" method="post">
			<div class="content_holder">
				<fieldset>
					<div class="form_help">
						<p>Choosing a new name which already exists will merge the two tags under the new name.</p>
					</div>
					<div class="content">
						<div class="row">
							<input type="hidden" value="{$old_tag}" name="old_tag" />
							<span class="false_label">Old Tag</span>
							<span class="false_input">{$old_tag|escape}</span>
						</div>
						<div class="row">
							<label for="new_tag">New Tag</label>
							<input type="text" id="new_tag" name="new_tag" />
						</div>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset class="cancel_or_save" id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Rename" />
							<span class="or"> or </span>
							<a class="action" href="/tags/by_tag?tag={$old_tag|urlencode}">Back to Tagged Items</a>
						</div>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>