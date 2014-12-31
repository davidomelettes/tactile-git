<?php

$file = 'http://chart.apis.google.com/chart?'.$_SERVER['QUERY_STRING'];
header("Content-Disposition: filename=chart.png");
header("Content-Type: image/png");
readfile($file);
