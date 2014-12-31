<?
class PriceFormatter implements FieldFormatter {
	public $is_safe=true;
	private $is_html=true;
	function __construct($html=true) {
		$this->is_html=$html;
	}
	function format($value) {
		return pricify($value,$this->is_html);
	}
}
?>
