<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Do you want to delete {$for|ucfirst} tagged: {foreach name=tags item=tag from=$selected_tags}&ldquo;{$tag|escape}&rdquo;{if !$smarty.foreach.tags.last} &amp; {/if}{/foreach}?</h2>
		</div>
		<form class="delete_form" action="/tags/process_delete_items" method="post">
			<div>
				{foreach from=$tag item=item name=tag_list}
				<input type="hidden" name="tag[]" value="{$item}" />
				{/foreach}
				<input type="hidden" name="for" value="{$for}" />
			</div>
			<div class="content_holder">
				<fieldset class="inline_checkbox">
					<div class="form_help">
						<p><span class="warning">WARNING!</span> This action will delete <strong>{$count} {$for}</strong> from Tactile CRM and all attached items (such as people,  opportunities, activies, notes and emails) - <strong>this cannot be undone</strong>.</p>
						{if $will_also_delete|@count > 0}
						<p>Due to dependancies, this will also...</p>
						<ul class="bullets" id="will_also_delete">
						{foreach from=$will_also_delete item=delete key=thing}
							<li>
								delete <strong>{$delete.count} {$thing}</strong>{if $delete.children}, which will in turn...
								<ul class="bullets">
								{foreach from=$delete.children item=delete_b key=thing_b}
									<li>delete <strong>{$delete_b.count} {$thing_b}</strong></li>
								{/foreach}
								</ul>
								{/if}
							</li>
						{/foreach}
						</ul>
						{/if}
						{if $account_orgs}
						<br/>
						<p>Of the above, the following organisation will <strong>not</strong> be deleted, because it is attached to your Tactile account:</p>
						<ul class="bullets will_not_delete">
						{foreach from=$account_orgs item=account}
							<li>{$account}</li>
						{/foreach}
						</ul>
						{/if}
						{if $user_people}
						<br/>
						<p>Of the above, the following people will <strong>not</strong> be deleted, because they are attached to Tactile user accounts:</p>
						<ul class="bullets will_not_delete">
						{foreach from=$user_people item=person}
							<li>{$person}</li>
						{/foreach}
						</ul>
						{/if}
					</div>
					<div class="content">
						<p>
							<label for="confirm" class="inline_input">Tick the box to confirm the deletion, <strong>this will be irreverisble</strong>.
							If you do this we will not be able to undo it.
							<input id="confirm" type="checkbox" class="checkbox" name="confirm" /></label>
						</p>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
		    	<fieldset class="cancel_or_save" id="save_container">
					<input type="submit" class="submit" value="Delete These {$for|ucfirst}" style="width: auto;" />
					<span>or</span>
					<a href="/tags/by_tag?{foreach from=$selected_tags item=t name=back}tag[]={$t|urlencode}{if !$smarty.foreach.back.last}&amp;{/if}{/foreach}" class="action">Back to Tagged Items</a>
		    	</fieldset>
	    	</div>
		</form>
	</div>
</div>
