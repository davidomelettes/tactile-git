{if $show_user_box}
	{foldable key="reports" title="Update Report"}
		<div>
			<form class="saveform" action="" method="get">
				<fieldset class="sidebar">
					<div class="row">
						<label for="user">User</label>
						<select id="user" name="user">
							{assign var=start_users value=false}
							{assign var=start_groups value=false}
							{foreach from=$user_list key=key item=value}
								{if !$start_users && '/^[^!*]/'|preg_match:$key}
									{assign var=start_users value=true}
									<optgroup label="Users">
								{/if}
								{if !$start_groups && '/^@/'|preg_match:$key}
									{assign var=start_groups value=true}
									<optgroup label="Groups">
								{/if}
								<option value="{$key}"{if $key eq $selected_user} selected="selected"{/if}>{$value}</option>
							{/foreach}
							</optgroup>
						</select>
					</div>
					{if $action eq 'pipeline_report'}
					<div class="row">
						<label for="include_old">Include Old</label>
						<select id="include_old" name="include_old">
							<option value="yes"{if $include_old eq "yes"} selected="selected"{/if}>Yes - Opportunities with an expected close date before this month</option>
							<option value="no"{if $include_old eq "no"} selected="selected"{/if}>No - Don't include Opportunities with an expected close date before this month</option>
						</select>
					</div>
					{/if}
					{if $action eq 'sales_history'}
					<div class="row">
						<label for="field_ending">Ending</label>
						<input id="field_ending" type="text" class="datefield" name="date" value="{$chart_date}" />
					</div>
					{/if}
					{if $action eq 'sales_report'}
					<div class="row">
						<label for="field_starting">Starting</label>
						<input id="field_starting" type="text" class="datefield" name="date" value="{$chart_date}" />
					</div>
					<div class="row">
						<label for="report_length">Period</label>
						<select id="report_length" name="report_length">
							<option value="21 days"{if $report_length eq '21 days'} selected="selected"{/if}>7/14/21 Days</option>
							<option value="90 days"{if $report_length eq '90 days'} selected="selected"{/if}>30/60/90 Days</option>
							<option value="9 months"{if $report_length eq '9 months'} selected="selected"{/if}>3/6/9 Months</option>
							<option value="12 months"{if $report_length eq '12 months'} selected="selected"{/if}>4/8/12 Months</option>
						</select>
					</div>
					{/if}
					<div class="row">
						<input id="submit" class="submit" type="submit" value="Redraw" />
					</div>
				</fieldset>
			</form>
		</div>
	{/foldable}
{/if}
