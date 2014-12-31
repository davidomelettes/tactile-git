<?php
/**
 *
 * @author gj
 */
class OmeletteURLFormatter extends URLFormatter {
	
	public function format($value) {
		if (empty($value)) {
			return '';
		}
		preg_match('#^https?://#i', $value, $matches);
		$protocol = !empty($matches[0]) ? $matches[0] : 'http://';
		$url = preg_replace('#^(?:https?://)?#i', '', $value);
		if (strlen($url) > 28) {
			$display = substr($url, 0, 25) . '...';
		} else {
			$display = $url;
		}
		return sprintf('<a class="sprite sprite-website out" href="%s">%s</a>', $protocol . $url, $display);
	}
	
}
