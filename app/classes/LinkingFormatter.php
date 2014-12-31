<?php

/**
 * Responsible for formatting a string into a URL, for example to construct a link to a filtered view
 * 
 * @author gj
 */
class LinkingFormatter implements FieldFormatter {

	public $is_safe = true;
	
	/**
	 * The url template to put the value into
	 *
	 * @var String
	 */
	protected $url_template;
	
	/**
	 * LinkingFormatter takes a URL-template string, with a '%s'
	 * that will be replaced by the url-encoded value during formatting
	 * e.g. 
	 * $f = new LinkingFormatter('/people/filter_by/?job_title=%s');
	 * $value='Web Developer';
	 * $value = $f->format($value);
	 * //$f == '<a href="/people/filter_by/?job_title=Web+Developer">Web Developer</a>'
	 * 
	 * @param $url_template String
	 */
	function __construct($url_template) {
		$this->url_template = $url_template;
	}

	/**
	 * Return an 'a' tag, with href set to the interpolated url-template, and the innerHTML being $value
	 * 
	 * @param String $value 
	 * @return String 
	 * @see FieldFormatter::format()
	 * @return String
	 */
	function format($value) {
		if(empty($value)) {
			return '';
		}
		$url = sprintf($this->url_template,urlencode($value));
		$html = '<a href="'.$url.'">'.h($value).'</a>';
		return $html;
	}
}

?>
