<div id="the_page" class="suspension_page">
	<div id="page_title">
		<h2>Your account is suspended</h2>
	</div>
	<div class="content_holder">
		<p>Two attempts to take payment from your account failed, and so until we are provided with new, valid details you're not going to be able to access your account.</p>
		{if $current_user->isAccountOwner()}
		<p><a class="action" href="/suspension/take_payment/">Click here to make a payment</a></p>
		{else}
		<p>Please tell your admin user that they need to login and enter new card details.</p>
		{/if}
	</div>
</div>