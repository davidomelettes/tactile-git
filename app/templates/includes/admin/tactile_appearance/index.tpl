<div id="right_bar">
	{foldable title="Login Page" key="login_logo"}
		{if !$logo_url}
		<p>Upload an image file (max 268x75) from your computer to display a logo on your login screen:</p>
		<form action="/appearance/save_file" method="post" enctype="multipart/form-data">
			<input type="hidden" name="is_account_logo" value="yes" />
			<p>
				<label for="upload_file"></label>
				<input class="file" type="file" name="Filedata" id="upload_file" />
			</p>
			<p>
				<input type="submit" class="submit" value="Upload" />
			</p>
		</form>
		{else}
		<p class="t-center">
			<img src="{$logo_url}" alt="{$user_company_name|escape}" title="{$user_company_name|escape}" />
		</p>
		<form action="/appearance/delete_logo" class="t-center">
			<p>
				<input type="submit" class="submit" value="Delete Logo" />
			</p>
		</form>
		{/if}
	{/foldable}
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable title="Sample Graph" key="sample_graph"}
		{$sample_graph->outputImg(268, 133)}
	{/foldable}
	{foldable key="appearance_overview_help" title="Appearance Help" extra_class="help"}
		<p>Selecting a theme will apply it to all Users of your account.</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="admin_holder">
		<div id="page_title">
			<h2>Customise Appearance</h2>
		</div>
		<div class="content_holder">
			<h3>Select Theme</h3>
			<ul id="appearance_theme_list" class="admin_list">
				<li class="custom">
					<h4><a class="group" href="/appearance/save_theme/?theme=custom">Custom</a></h4>
					<div class="colours"><div class="colour_swatch a" style="background-color: {$primary};"></div><div class="colour_swatch b" style="background-color: {$secondary};"></div></div>
				</li>
				<li class="green">
					<h4><a class="group" href="/appearance/save_theme/?theme=green">Green</a></h4>
					<div class="colours"><div class="colour_swatch a"></div><div class="colour_swatch b"></div></div>
				</li>
				<li class="blue">
					<h4><a class="group" href="/appearance/save_theme/?theme=blue">Blue</a></h4>
					<div class="colours"><div class="colour_swatch a"></div><div class="colour_swatch b"></div></div>
				</li>
				<li class="red">
					<h4><a class="group" href="/appearance/save_theme/?theme=red">Red</a></h4>
					<div class="colours"><div class="colour_swatch a"></div><div class="colour_swatch b"></div></div>
				</li>
				<li class="grey">
					<h4><a class="group" href="/appearance/save_theme/?theme=grey">Grey</a></h4>
					<div class="colours"><div class="colour_swatch a"></div><div class="colour_swatch b"></div></div>
				</li>
				<li class="orange">
					<h4><a class="group" href="/appearance/save_theme/?theme=orange">Orange</a></h4>
					<div class="colours"><div class="colour_swatch a"></div><div class="colour_swatch b"></div></div>
				</li>
				<li class="purple">
					<h4><a class="group" href="/appearance/save_theme/?theme=purple">Purple</a></h4>
					<div class="colours"><div class="colour_swatch a"></div><div class="colour_swatch b"></div></div>
				</li>
			</ul>
		</div>
	</div>
</div>