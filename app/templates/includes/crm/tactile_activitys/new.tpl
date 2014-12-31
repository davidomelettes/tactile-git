{if !$partial}
<div id="right_bar">
</div>
{/if}
<div id="{if $partial}partial_page{else}the_page{/if}">
	<div class="edit_holder" id="new_activity">
		<div id="page_title">
			{if $Activity->id}
			<h2>Editing {$Activity->getFormatted('name')}</h2>
			{else}
			<h2>New Activity</h2>
			{/if}
		</div>
		<form action="/activities/save/" method="post" class="saveform">
			{with model=$Activity}
			<div class="content_holder">
				<fieldset id="activity_basic_info">
					<h3>Activity Details</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>Name is the only required field.</p>
					</div>
					<div class="content">
						{input type="hidden" attribute="id"}
						{input type="text" attribute="name" label="Summary"}
						{input type="hidden" attribute="organisation_id"}
						{input type="text" attribute="organisation"}
						{input type="hidden" attribute="person_id"}
						{input type="text" attribute="person"}
						{input type="hidden" attribute="opportunity_id"}
						{input type="text" attribute="opportunity"}
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<div class="form_help">
					<p>A 'To Do' has a single due date, while an 'Event' can have a location, start, and end date.</p>
				</div>
				<fieldset id="activity_new_inline" class="activity_fs {if $Activity->isEvent()}event{else}todo{/if}_{if $date_selected == 'later'}later{elseif $date_selected == 'date'}date{else}t{/if}">
					<div class="row">
						<label for="activity_select_class">This is a...</label>
						<select name="Activity[class]" id="activity_select_class" tabindex="4">
							<option value="todo"{if !$Activity->isEvent()} selected="selected"{/if}>To Do</option>
							<option value="event"{if $Activity->isEvent()} selected="selected"{/if}>Event</option>
						</select>
					</div>
					<div id="activity_location_container" class="row">
						<label for="activity_location" id="activity_location_label">Location</label>
						<input type="text" id="activity_location" name="Activity[location]"	value="{$location}" tabindex="5" />
					</div>
					<div class="row">
						<label for="activity_select_date">When</label>
						
						<div id="when_container" class="{if $Activity->isEvent()}event{else}todo{/if}_{if $date_selected == 'later'}later{elseif $date_selected == 'date'}date{else}t{/if}">
							<select id="activity_select_date" name="Activity[date_choice]" class="date_select{if (!$Activity->isEvent() && $date_selected eq 'date') || ($Activity->isEvent() && $date_selected ne 'later')}_long{/if}" tabindex="6">
								<option value="today" {if $date_selected eq 'today'}selected="selected"{/if}>Today</option>
								<option value="tomorrow" {if $date_selected eq 'tomorrow'}selected="selected"{/if}>Tomorrow</option>
								<option value="date" {if $date_selected eq 'date'}selected="selected"{/if}>Choose a Date...</option>
								<option value="later" {if $date_selected eq 'later'}selected="selected"{/if}>Later</option>
							</select>
						
							<div id="datetime_container">
								<div id="activity_datetime">
									<input type="text" id="activity_date" name="Activity[date]" class="date" value="{$date}" />
									<input type="hidden" id="activity_time_minutes_hidden" name="Activity[time_minutes]" value="{$minutes}" />
									<input type="text" id="activity_time_minutes" class="time minutes" value="{$minutes}" tabindex="8" />
									<input type="hidden" id="activity_time_hours_hidden" name="Activity[time_hours]" value="{$hours}" />
									<input type="text" id="activity_time_hours" class="time hours" value="{$hours}" tabindex="7" />
								</div>
								
								<div id="time_range_mark">to</div>	
								
								<div id="activity_end_datetime">
									<input type="text" id="activity_end_date" name="Activity[end_date]" class="date" value="{$end_date}" />
									<input type="hidden" id="activity_end_time_minutes_hidden" name="Activity[end_time_minutes]" value="{$end_minutes}" />
									<input type="text" id="activity_end_time_minutes" class="time minutes" value="{$end_minutes}" />
									<input type="hidden" id="activity_end_time_hours_hidden" name="Activity[end_time_hours]" value="{$end_hours}" />
									<input type="text" id="activity_end_time_hours" class="time hours" value="{$end_hours}" />
								</div>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<fieldset>
					<div class="form_help">
						<p>{if $current_user->isAdmin()}
						You can change the available Type options from the <a href="/setup/?group=activities&amp;option=type">Admin</a> page.
						{else}
						Your account admin can customise the available Type options from the Admin page.
						{/if}</p>
					</div>
					<div class="content">
						{select attribute="type_id"}
						{select attribute="assigned_to"}
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<fieldset id="activity_description">
					<h3>Description</h3>
					<div class="content">
						<div class="row">
							<textarea name="Activity[description]" id="Activity_description" rows="4" cols="20">{$Activity->description}</textarea>
						</div>
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
			{/with}
		</form>
	</div>
</div>
