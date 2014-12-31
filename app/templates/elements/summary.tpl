{with model=$model}
	<ul class="summary_fields">
		<li class="odd single description{if $model->get_name() eq 'Organisation' || $model->get_name() eq 'Person' || $model->get_name() eq 'Opportunity' || $model->get_name() eq 'Activity'} inline_edit{/if}" id="{$model->get_name()|strtolower}_description"{if $model->get_name() eq 'Organisation' || $model->get_name() eq 'Person' || $model->get_name() eq 'Opportunity' || $model->get_name() eq 'Activity'} title="Click to edit"{/if}>
			<span class="view_label"><span class="label">Description</span></span>
			<div class="view_data">
				<span>{if $model->description|trim eq ''}<span class="blank">-</span>{else}<span class="data">{$model->description|escape|nl2br}</span>{/if}</span>
			</div>
		</li>
		<li class="spacer"> </li>

	{foreach from=$summary_groups item=summary_fields}
		{foreach from=$summary_fields item=field key=key name=summary}
		{if $key|is_numeric}
		{assign var=row_id value=$model->get_name()|strtolower|cat:'_'|cat:$field}
		{else}
		{assign var=row_id value=$model->get_name()|strtolower|cat:'_'|cat:$key}
		{/if}
		<li id="{$row_id}" class="{if $smarty.foreach.summary.index % 2 == 0}odd{else}even{/if}{if $smarty.foreach.summary.total == 1} single{/if}">
			{if $key|is_numeric}
			{view_data attribute=$field}
			{else}
			{view_data attribute=$key label=$field}
			{/if}
		</li>
		{/foreach}
		<li class="spacer"> </li>
	{/foreach}
	
		{include file="elements/custom_fields.tpl" for=$model->get_name()|strtolower}
	
		<li class="odd" id="{$model->get_name()|strtolower}_read">
			<span class="view_label"><span>Viewable</span></span><span class="view_data"><span>{$model->getReadString()}</span></span>
		</li>
		<li class="even" id="{$model->get_name()|strtolower}_created">
			{if $model->get_name() == 'User'}
			{assign var=user_person value=$model->getPerson()}
			<span class="view_label"><span>Created By</span></span><span class="view_data"><span>{$user_person->getFormatted('owner')}, {$user_person->getFormatted('created')}</span></span>
			{else}
			<span class="view_label"><span>Created By</span></span><span class="view_data"><span>{$model->getFormatted('owner')}, {$model->getFormatted('created')}</span></span>
			{/if}
		</li>
		<li class="odd" id="{$model->get_name()|strtolower}_write">
			<span class="view_label"><span>Editable</span></span><span class="view_data"><span>{$model->getWriteString()}</span></span>
		</li>
		{if $model->created neq $model->lastupdated}
		<li class="even" id="{$model->get_name()|strtolower}_updated">
			<span class="view_label"><span>Updated By</span></span><span class="view_data"><span>{$model->getFormatted('alteredby')}, {$model->getFormatted('lastupdated')}</span></span>
		</li>
		{/if}
	</ul>
{/with}
