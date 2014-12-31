<div id="right_bar">
	{foldable key="import_overview_help" title="Import Help" extra_class="help"}
		<p>Importing Contacts is the fastest way to add multiple Organisations and People to Tactile.</p>
		<p>Imports can be tagged, allowing you to easily view and manage the results. It's a great way to help Tactile undo any mistakes you may make.</p>
	{/foldable}
	{foldable key="import_feedback" title="Feedback"}
		<p>Want to import from another program?</p>
		<p><a href="mailto:support@tactilecrm.com?Subject=Import Types">Let us know</a>!</p>
	{/foldable}
	{if $import_tags}
	{foldable key="import_tags" title="Recent Imports"}
		<ul class="sidebar_options">
			{foreach from=$import_tags item=tag}
			<li><a href="/tags/by_tag/?tag[0]={$tag.name}">{$tag.name|truncate:20}</a></li>
			{/foreach}
		</ul>
	{/foldable}
	{/if}
</div>
<div id="the_page">
	<div class="edit_holder">
        <div id="page_title">
        	<h2>Import Organisations and People into Tactile</h2>
        </div>

     	  	<div class="admin_holder">
        		<div class="content_holder">
					<h3>What do you want to import?</h3>
					<ul class="admin_list">
						<li>
							<h4><a class="group" href="/import/csv/">CSV File &raquo;</a></h4>
							<p>Choose this option if you are importing from Outlook or Excel, or any other Comma Separated Values file.</p>
						</li>
						<li>
							<h4><a class="group" href="/import/vcard/">vCard File &raquo;</a></h4>
							<p>vCards are used by Apple's Address Book application.</p>
						</li>
						<li>
							<h4><a class="group" href="/import/cloud/">Cloud Contacts &raquo;</a></h4>
							<p>Choose this option if you are importing from a Cloud Contacts export file.</p>
						</li>
						<li>
							<h4><a class="group" href="/import/google/">Google Contacts &raquo;</a></h4>
							<p>If you store your contacts online with Google, choose this option.</p>
						</li>
						{if $fb_accountname}
						<li>
							<h4><a class="group" href="/import/freshbooks/">FreshBooks Clients &raquo;</a></h4>
							<p>Import your clients from FreshBooks.</p>
						</li>
						{/if}
						<li>
							<h4><a class="group" href="/import/shoeboxed/">Shoeboxed Contacts &raquo;</a></h4>
							<p>Import from business card data stored with Shoeboxed.</p>
						</li>
						{if $current_user->isAdmin()}
						<li>
							<h4><a class="group" href="/import/highrise/">Highrise Data &raquo;</a></h4>
							<p>Import your Highrise users, contact, deals and groups.</p>
						</li>
						{/if}
						
					</ul>
				</div>
			</div>

	</div>
</div>
