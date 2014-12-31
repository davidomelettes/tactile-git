<div id="right_bar">
	{foldable}
		<p><a href="/tracks/">Back to Track List</a></p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>New Track</h2>
		</div>
		<form action="/tracks/save/" method="post" class="saveform">
			<div class="content_holder">
				<fieldset>
					<h3>Track Details</h3>
					<div class="content">
						{with model=$ActivityTrack}
						{input type="hidden" attribute="id"}
						{input type="text" attribute="name"}
						<div class="row">
							<label for="ActivityTrack_description">Description</label>
							<textarea id="ActivityTrack_description" name="ActivityTrack[description]">{$smarty.session._controller_data.ActivityTrack.description|stripslashes|default:$ActivityTrack->description}</textarea>
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
