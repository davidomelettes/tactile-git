<div id="right_bar">
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Add the {$track->name|escape} Track to {if $model->get_name() eq 'Person'}{$model->fullname|escape}{else}{$model->getFormatted('name')}{/if}</h2>
		</div>
		<form action="/{$for}/save_activity_track/{$model->id}" method="post" class="saveform">
			<div><input type="hidden" name="track_id" value="{$track->id}" /></div>
			{foreach from=$track->stages item=stage name=stages}
			<div class="content_holder">
				<h3>Stage {$smarty.foreach.stages.iteration}</h3>
				<div class="content">
					{with model=$stage}
					{input type="text" attribute="name" number=$stage->id}
					<div class="row">
						<label for="activitytrackstage_{$stage->id}_date">Date</label>
						<input class="datefield" type="text" id="activitytrackstage_{$stage->id}_date" name="ActivityTrackStage[{$stage->id}][date]" value="{$stage->getDueDate()}" />
					</div>
					{select attribute="assigned_to" number=$stage->id}
					{select attribute="type_id" number=$stage->id}
					{/with}
					<div class="row">
						<label for="activitytrackstage_{$stage->id}_description">Description</label>
						<textarea id="activitytrackstage_{$stage->id}_description" name="ActivityTrackStage[{$stage->id}][description]">{$stage->description|escape}</textarea>
					</div>
				</div>
			</div>
			{/foreach}
			
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Add Activity Track" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>
