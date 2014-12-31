<form action="/activities/save/" class="side_form">
	{with model=$Activity tags='none'}
	<fieldset class="sidebar">
		{input type="text" attribute="name"}
		{input type="hidden" attribute="organisation_id"}
		
		{input type="hidden" attribute="person_id"}
		{if !$Person->id}
			{input type="text" attribute="person" value=$Activity->person}
		{/if}
	</fieldset>
		
	<fieldset class="sidebar">
		<label for="activity_class">This is a...</label>
		<select name="Activity[class]" id="activity_class">
			<option value="todo">To Do</option>
			<option value="event">Event</option>
		</select>
		<label for="activity_location" id="activity_location_label" style="display:none;">Location</label>
		<input type="text" id="activity_location" name="Activity[location]" value="{$location}" style="display:none;" />
		<label for="activity_date_select">When</label>
		
		<div style="float: left;">
			<select id="activity_select_date" name="Activity[date_choice]" class="date_select">
				<option value="today" {if $selected eq 'today'}selected="selected"{/if}>Today</option>
				<option value="tomorrow" {if $selected eq 'tomorrow'}selected="selected"{/if}>Tomorrow</option>
				<option value="date" {if $selected eq 'date'}selected="selected"{/if}>Choose a Date...</option>
				<option value="later" {if $selected eq 'later'}selected="selected"{/if}>Later</option>
			</select>
			
			<div id="datetime_container" style="float: left;">
				<div id="activity_datetime" style="float: left;">
					<input type="text" id="activity_date" name="Activity[date]" class="date" value="{$date}" {if !$date_selected && $date_selected ne 'date'}style="display:none;" {/if}/>
					<input type="hidden" id="activity_time_hours_hidden" name="Activity[time_hours]"
						value="{$hours}" />
					<input type="text" id="activity_time_hours" class="time hours"
						value="{$hours}" {if $date_selected && $date_selected eq 'later'}style="display:none;" {/if}/>
					<input type="hidden" id="activity_time_minutes_hidden" name="Activity[time_minutes]"
						value="{$minutes}" />
					<input type="text" id="activity_time_minutes" class="time minutes"
						value="{$minutes}" {if $date_selected && $date_selected eq 'later'}style="display:none;" {/if}/>
				</div>
		
				<div id="time_range_mark" style="display: none; float: left;">to</div>	
			
				<div id="activity_end_datetime" style="float: left;">	
					<input type="text" id="activity_end_date" name="Activity[end_date]" class="date" style="display: none;" />
					<input type="hidden" id="activity_end_time_hours_hidden" name="Activity[end_time_hours]"
						value="{$end_hours}" />
					<input type="text" id="activity_end_time_hours" class="time hours"
						value="{$end_hours}" style="display: none;" />
					<input type="hidden" id="activity_end_time_minutes_hidden" name="Activity[end_time_minutes]"
						value="{$end_minutes}" />
					<input type="text" id="activity_end_time_minutes" class="time minutes"
						value="{$end_minutes}" style="display: none;" />
				</div>
			</div>
		</div>
	</fieldset>
	
	<fieldset class="sidebar">
		{if $Activity->organisation_id}
			{select attribute="opportunity_id" constraint="organisation_id,=,`$Activity->organisation_id`"}
		{else}
			{select attribute="opportunity_id"}
		{/if}
		{select attribute="assigned_to"}
	</fieldset>
	<fieldset class="sidebar save"  style="width: 100%;">
		<a class="more" href="/activities/new/?{strip}
			{if $controller_data.opportunity_id}opportunity_id={$controller_data.opportunity_id}
			{else}
			{if $controller_data.organisation_id}organisation_id={$controller_data.organisation_id}&amp;{/if}
			{if $controller_data.person_id}person_id={$controller_data.person_id}{/if}
			{/if}
			"{/strip}>More Options</a>
		<div class="cancel_or_save">
			<a href="#" class="cancel">Cancel</a>
			<span class="or"> or </span>
			<input type="submit" value="Save" class="button"/>
		</div>
	</fieldset>
	{/with}
	<div style="clear: both;"></div>
</form>
