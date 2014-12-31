
{form controller=$self.controller action="index" notags=true}
<fieldset class="quicksearch">
	<legend title="Quick Search">Quick Search</legend>
	<input type="text" name="quicksearch" />
	<select name="quicksearchfield">
		{foreach  key=key item=item from=$collection->getDisplayFieldNames()}
			<option value="{$key}">{$item}</option>
		{/foreach}
	</select>
	<input type="submit" name="submit" value="Go" title="Start new search"/><input type="submit" name="submit" value="Refine" title="Add constraint to search"/>
</fieldset>
{/form}

