<div id="right_bar">
	{foldable}
		<p><a href="/tracks/">Back to Track List</a></p>
	{/foldable}
</div>
<div id="the_page">
	<div id="track_view" class="view_holder">
		<div id="page_title">
			<h2>
				{$ActivityTrack->name|escape}
			</h2>
			{include file="elements/edit_delete.tpl" url="tracks" for="user" model=$ActivityTrack text="Track"}
		</div>
		<div class="tag_list">&nbsp;</div>
		<div class="content_holder" id="summary_info">
			<div class="content">
				{include file="elements/summary.tpl model=$ActivityTrack}
			</div>
		</div>
		<div class="content_holder" id="stage_info">
		<h3>Stages</h3>
			<p><a class="sprite sprite-add action" href="/tracks/new_stage?track_id={$ActivityTrack->id}">Add a stage</a></p>
			<table class="index_table">
				<thead>
					<tr>
						<th>Stage Name</th>
						<th>Due in X Days</th>
						<th>Type</th>
						<th>Assigned To</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$ActivityTrack->stages item=stage}
					<tr>
						<td>{$stage->name|escape}</td>
						<td>{$stage->x_days}</td>
						<td>{$stage->type|escape}</td>
						<td>{$stage->getFormatted(assigned_to)}</td>
						<td><a class="action" href="/tracks/edit_stage/{$stage->id}?track_id={$ActivityTrack->id}">Edit</a> <a class="action delete" href="/tracks/delete_stage/{$stage->id}">Delete</a></td>
					</tr>
					{foreachelse}
					<tr>
						<td colspan="5">No stages yet</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
</div>
