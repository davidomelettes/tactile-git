/*
* Smarty plugin
* -------------------------------------------------------------
* Type: modifier
* Name: javascript
* Purpose: concatinates two strings
* -------------------------------------------------------------
*/
function smarty_javascript($params, &$smarty)
{

$code = '';

$code .= '<script LANGUAGE="JavaScript">\nfunction confirmSubmit(){\nvar agree=confirm("Are you sure you wish to continue?");\nif (agree)\nreturn true ;\nelse\nreturn false ;\n}\n</script>';


return $code;
}
