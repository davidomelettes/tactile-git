<?php

define('FILE_ROOT', '../');

$start = !empty($_GET['s']) ? $_GET['s'] : '#0F5E15';
$end = !empty($_GET['e']) ? $_GET['e'] : '#569C30';

require_once '../app/classes/Gradient.php';
$img = new Gradient(1, 75, $start, $end);