<?php

class TestModelLoading extends OmeletteModelLoader  {

	protected $tests = array();
	
	/**
	 * 
	 * @see ModelLoading::load()
	 */
	public function load($modelname) {
		if(isset($this->tests[$modelname])) {
			$model = new $this->tests[$modelname];
		}
		else {
			$model = parent::load($modelname);
		}
		return $model;
	}
	
	public function useTest($modelname, $classname = null) {
		$this->tests[$modelname] = (is_null($classname) ? 'Test_'.$modelname : $classname);
	}
}

?>
