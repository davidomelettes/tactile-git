<?php
/**
 * Classes implementing this will be responsible for returning a DataObject to act as the Model
 * @author gj
 */
interface ModelLoading {
	/**
	 * Takes a String (name) and returns a model of some kind (DataObject)
	 */
	public function load($modelname);
}
?>