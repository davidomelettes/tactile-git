<?
class PercentageFormatter implements FieldFormatter {
	function format($value) {
		return h($value).'%';
	}
}
?>