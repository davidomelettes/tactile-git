<div class="edit_delete">
	{if $pinned}
	(Pinned to your dashboard)
	{else}
	<form action="/graphs/pin_to_dashboard" method="post">
		<div>
			<input type="hidden" name="chart_method" value="{$chart_method}" />
			<input type="submit" class="submit" value="Pin to Dashboard" />
		</div>
	</form>
	{/if}
</div>