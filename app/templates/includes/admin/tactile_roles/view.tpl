<script type="text/javascript">
	Tactile.role_id={$Role->id};
</script>
<div id="right_bar">
</div>
<div id="the_page">
	<div id="role_view" class="view_holder">
		<div id="page_title">
			<h2>{$Role->getFormatted('name')}</h2>
			{include file="elements/edit_delete.tpl" url="groups" for="group" model=$Role text="Group"}
			{if $Role->description}
			<p class="description">{$Role->getFormatted('description')}</p>
			{/if}
		</div>
		<div class="content_holder">
			<h3>Users in this Group</h3>
			<ul class="user_list">
			{foreach from=users item=user from=$Role->getMembers()}
				<li><a href="/users/view/{$user->username|urlencode}">{$user->getFormatted('username')}</a></li>
			{/foreach}
			</ul>
		</div>
	</div>
</div>
