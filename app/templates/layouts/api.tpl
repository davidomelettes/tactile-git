<#if $flash->hasErrors()#>
{"status":"error", "messages": <#$flash->getErrorsAsJson()#>}
<#else#>
<#include file="$templateName"#>
<#/if#>