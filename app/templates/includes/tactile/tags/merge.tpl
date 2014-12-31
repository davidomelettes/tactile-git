<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Do you want to merge &ldquo;{$old_tag|escape}&rdquo; with &ldquo;{$new_tag|escape}&rdquo;?</h2>
		</div>
		<form class="saveform" action="/tags/dorename" method="post" id="tag_action_form">
			<input type="hidden" name="confirm_merge" value="yes" />
			<input type="hidden" value="{$old_tag}" name="old_tag" />
			<input type="hidden" value="{$new_tag}" name="new_tag" />
			<div class="content_holder">
				<fieldset>
					<div class="form_help">
						<p>You have requested to rename the tag &ldquo;{$old_tag|escape}&rdquo; to &ldquo;{$new_tag|escape}&rdquo;, which is an existing tag.</p>
						<p>Continuing will merge the two tags together, under the new name.</p>
					</div>
					<div class="content">
						<div class="row">
							<span class="false_label">Old Tag</span>
							<span class="false_input">{$old_tag|escape}</span>
						</div>
						<div class="row">
							<span class="false_label">New Tag</span>
							<span class="false_input">{$new_tag|escape}</span>
						</div>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset class="cancel_or_save" id="save_container">
					<div class="content">
						<div class="row">
						<input type="submit" value="Merge" />
						<span>or</span>
						<a class="action" href="/tags/by_tag?tag={$old_tag|urlencode}">Back to Tagged Items</a>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>