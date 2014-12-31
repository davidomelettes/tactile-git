<div id="right_bar">
	{if $new_tags}
	<div class="foldable" id="new_tags">
		<h3>Newest Tags</h3>
		<div>
			<ul id="new_tag_list" class="sidebar_options">
				{foreach name="new_tags" item=tag from=$new_tags}
					<li><a href="/tags/by_tag?tag={$tag|urlencode}">{$tag|escape}</a></li>
				{/foreach}	
			</ul>
		</div>
	</div>
	{/if}
    {foldable key="tag_overview_help" title="Tag Help" extra_class="help" help_url="http://www.tactilecrm.com/help"}
    <p>Tags are labels you can use to describe items (Organisations, People, etc.) in Tactile. An item can have as many tags as you like.</p>
    <p>Your most popular tags are shown to the left in alphabetical order. A bigger text-size means the tag is used on more items.</p>
    <p>Clicking on a tag will show you all the items that have been given that tag.</p>
    {/foldable}
    {if $current_user->isAdmin()}
    {foldable key=tag_everything title="Tag Everything"}
    <p>To tag everything in your account, enter an unused tag below.</p>
    <form action="/tags/tag_everything/" action="post" class="sideform">
    <p>
    	<label for="everything_tag">New Tag:</label>
    	<input id="everything_tag" class="text" type="text" name="tag" />
    	<input class="submit" type="submit" value="Add" />
    </p>
    </form>
    {/foldable}
    {/if}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			<h2>Tags</h2>
		</div>
		<div id="tag_cloud">
			{foreach name=tag_cloud item=size key=tag from=$tags}
			<span class="tag_band{$size}"><a href="/tags/by_tag?tag={$tag|urlencode}">{$tag}</a></span>
			{foreachelse}
			<p>You don't have any tags. Click 'Add Tags' when viewing an item to add some.</p>
			{/foreach}
		</div>
	</div>
</div>
