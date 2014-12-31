<div id="right_bar">
	{foldable}
		<p><a href="/tracks/view/{$ActivityTrack->id}">Back to {$ActivityTrack->name|escape} Track</a></p>
	{/foldable}
	{foldable extra_class="help" title="Activity Track Stage Help" key="activity_track_stage_help"}
		<p>Each "Stage" in an Activity Track represents a template for an individual Activity.</p>
		<p>The "Due In X Days" field can be used to automatically set the due dates for Actitivies created through Tracks.
		For example, placing "0" here means the same as "today", and "1" means the same as "tomorrow".</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>New Track Stage</h2>
		</div>
		<form action="/tracks/save_stage?track_id={$ActivityTrack->id}" method="post" class="saveform">
			<div class="content_holder">
				<fieldset>
					<h3>Track Details</h3>
					<div class="content">
						{with model=$ActivityTrackStage}
						{input type="hidden" attribute="id"}
						{input type="hidden" attribute="track_id" value=$ActivityTrack->id}
						{input type="text" attribute="name"}
						{input type="text" attribute="x_days" label="Due in x days"}
						{select attribute="assigned_to"}
						{select attribute="type_id"}
						<div class="row">
							<label for="ActivityTrackStage_description">Description</label>
							<textarea id="ActivityTrackStage_description" name="ActivityTrackStage[description]">{$smarty.session._controller_data.ActivityTrackStage.description|stripslashes|default:$ActivityTrackStage->description}</textarea>
						</div>
						{/with}
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Save" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>
