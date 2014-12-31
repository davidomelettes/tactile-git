<?php
/**
 * Classes implementing this will be responsible for taking a hash and building a url from it
 * @author gj
 */
interface LinkBuilding {
	/**
	 * Takes a series of paramaters and a 'value' and returns a String resembling a url
	 * @param Array $params
	 * @param String [$data]
	 */
	public function build($params,$data=false);
}
?>