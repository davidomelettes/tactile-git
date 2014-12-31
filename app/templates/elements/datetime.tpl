<label for="{$date_id}_select">{$label}</label>
<select id="{$date_id}_select" class="datetime_select">
<option value='today'{if $is_today}selected="selected"{/if}>Today</option>
<option value='tomorrow'{if $is_tomorrow}selected="selected"{/if}>Tomorrow</option>
<option value='this_week'{if $this_week}selected="selected"{/if}>This Week</option>
<option value='later' {if !$is_today && !$is_tomorrow && !$this_week && $date_value}selected="selected"{/if}>Later...</option>
</select>
<input type="text" id="{$date_id}" class="date{$classes}" name="{$name}" value="{$date_value}" {if $is_today || $is_tomorrow || $this_week || $date_value eq ''}style="display:none;"{else}style="clear:left;margin-left:150px;"{/if}/>
<input type="text" id="{$date_id}_hours" class="time hour{$classes}" name="{$hour_name}" value="{$hour_value}" />
<input type="text" id="{$date_id}_minutes" class="time minute{$classes}" name="{$minute_name}" value="{$minute_value}" />