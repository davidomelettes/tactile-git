{foldable title="Extra Information" key="extra_information"}
	<p><span class="lighter">Created by</span> <strong>{$model->getFormatted('owner')}</strong>, {$model->getFormatted('created')}<br />
	{if $model->created neq $model->lastupdated}
	<span class="lighter">Updated by</span> <strong>{$model->getFormatted('alteredby')}</strong>, {$model->getFormatted('lastupdated')}
	{/if}
	</p>
	<p>This {$type} is viewable 
		{if $model->getReadString() eq $model->getWriteString()} and editable {/if}
		<strong>{$model->getReadString()}</strong>
		{if $model->getReadString() neq $model->getWriteString()}and editable 
		<strong>{$model->getWriteString()}</strong>{/if}
	</p>
{/foldable}