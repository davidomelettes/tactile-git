<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable key="activity_tracks_help" title="Activity Tracks Help" extra_class="help"}
		<p>Activity Tracks  allow multiple Activities to be created at once.</p>
		<p>Each Activity in the sequence can be specified to happen in a set number of days in the future, and be assigned to a particular User.</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/paging.tpl" for="tracks" add="track" add_text="Add New Track"}
			<h2>Activity Tracks</h2>
		</div>
		<div id="page_main">
			<table id="track_index" class="index_table">
				<thead>
					<tr>
						<th class="primary">Name</th>
						<th>Stages</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th class="primary">Name</th>
						<th>Stages</th>
					</tr>
				</tfoot>
				<tbody>
					{foreach name=tracks item=track from=$activitytracks}
					<tr>
						<td class="primary">
							<a href="/tracks/view/{$track->id}" class="sprite-med sprite-activity_med">{$track->getFormatted('name')}</a>
						</td>
						<td>{$track->getStageCount()}</td>
					</tr>
					{foreachelse}
					<tr>
						<td>You don't have any Tracks at this time.</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			{if $num_pages > 1}
			<div class="bottom_paging">
				{include file="elements/paging.tpl" for="activities" add="activity" add_text="Add New Activity"}
			</div>
			{/if}
		</div>
	</div>
</div>
