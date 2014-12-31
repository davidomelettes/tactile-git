<?php

function smarty_modifier_bytes($string) {
	$bytes = (int) $string;
	$suffixes = array("B", "KB", "MB", "GB", "TB", "PB");
	$i = 0;
    while ($bytes >= 1024) {
        $i++;
        $bytes = $bytes/1024;
    }
    return number_format($bytes, ($i ? 2 : 0), ".", ",") . " " . $suffixes[$i];
}
