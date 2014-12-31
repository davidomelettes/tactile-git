<div id="the_page">
	<div class="view_holder">
		<div id="page_title">
			<h2>Your deletion request has been queued</h2>
		</div>
		<div class="content_holder">
			<div class="form_help">
				<p>You requested to delete all {$for|ucfirst} tagged: {foreach name=tags item=tag from=$selected_tags}&ldquo;{$tag|escape}&rdquo;{if !$smarty.foreach.tags.last} &amp; {/if}{/foreach}</p>
				<p>The action you've requested is quite large, so rather than make you wait we have scheduled it to run in the background. We'll send you an email when it's completed.
				This should only take a few minutes.</p>
				<p><strong>NOTE:</strong> Any items added to the tag list above, before the process has finished, may also be deleted!</p>
			</div>
		</div>
		<div class="content_holder">
			<p><a class="action" href="/tags">Back to Tag List</a></p>
		</div>
	</div>
</div>