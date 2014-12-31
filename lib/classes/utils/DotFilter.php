<?php
/**
 * Filters a DirectoryIterator to ignore hidden files (and . and ..)
 */
class DotFilter extends FilterIterator {
	function accept() {
		$file=$this->getInnerIterator()->current();
		return !(substr($file->getFilename(),0,1)=='.');
	}
}
?>