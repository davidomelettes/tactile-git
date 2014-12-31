<?php
/**
 * Responsible for the formatting of Boolean values as images
 * @author gj
 * @package Formatters
 */
class BooleanFormatter implements FieldFormatter {
	/**
	 * The formatter escapes html-bits by itself, so we need to let things know that
	 * @access public
	 * @var Boolean $is_safe
	 */
	public $is_safe=true;
	
	/**
	 * The folder containing the graphics to use
	 *
	 * @var string
	 */
	protected $graphics_path = '/themes/default/graphics/';
	
	/**
	 * If $value is 't', then use the 'true' image, otherwise 'false'
	 * 
	 * @param String $value
	 * @return String
	 */
	function format($value) {
		$value='<img src="'.$this->graphics_path.(($value=='t')?'true':'false').'.png" alt="'.$value.'" />';
		return $value;
	}
}
?>
