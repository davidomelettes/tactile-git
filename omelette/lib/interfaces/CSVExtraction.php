<?php
/**
 * Classes implementing this will be responsible from converting data from a given format into something EGS
 * understands, one row at a time.
 */
interface CSVExtraction {
	/**
	 * Takes a row of csv data, and the file's headings row, and extracts the data for Companies and People,
	 * and their respective Addresses and ContactMethods
	 * @param Array $headings
	 * @param Array $row
	 * @return Array An array of company- and person-data
	 */
	public function extract(Array $headings,Array $row);
}
?>