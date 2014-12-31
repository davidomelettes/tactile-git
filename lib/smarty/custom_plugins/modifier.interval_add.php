<?php
function smarty_modifier_interval_add($base=0,$add) {
	$base = new Interval($base);
	$add = new Interval($add);
	return $base->add($add)->getValue();
}
?>