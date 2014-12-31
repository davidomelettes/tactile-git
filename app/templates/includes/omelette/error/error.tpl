<h1>Oops! An Error Has Occurred</h1>
<p>Tactile CRM has encountered a problem and is unable to deliver the page you expected.
Details of this error have been sent to our technical team.</p>
<p>We recommend trying the following things:</p>
<ol>
	<li>Refresh the page</li>
	<li>Use your browser's 'back' button to return to where you came from, and try again later</li>
	<li>Return to <a href="/">your Dashboard</a>, or your <a href="/organisations">Organisations index</a></li>
	<li>If the problem persists, please contact our support team via <a href="mailto:support@tactilecrm.com">support@tactilecrm.com</a></li>
</ol>
{if $exception}
<div id="xd">
	<p><strong>DEVELOPMENT MODE IS ON</strong></p>
	<pre>{$exception->getMessage()}</pre>
	<pre>{$exception->getFile()} ({$exception->getLine()})</pre>
	<pre>{$exception->getTraceAsString()}</pre>
</div>
{/if}