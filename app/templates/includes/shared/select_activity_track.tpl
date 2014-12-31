<div id="right_bar">
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Select an Activity Track to Add</h2>
		</div>
		<form action="/organisations/add_activity_track/{$model->id}" method="get" class="saveform">
			<div class="content_holder">
				<div class="content">
					<div class="row">
						<label for="select_track_id">Activity Track</label>
						<select id="select_track_id" name="track_id">
							{foreach from=$activity_tracks item=track key=id}
							<option value="{$id}">{$track|escape}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			
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
