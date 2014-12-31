<?php
class OmeletteModelLoader implements ModelLoading {
	public function load($modelname) {
		if(class_exists(APP_NAME.'_'.$modelname)) {
			$modelname =APP_NAME.'_'.$modelname; 
			$model = new $modelname;
		}
		else if(class_exists('Omelette_'.$modelname)) {
			$modelname='Omelette_'.$modelname;
			$model = new $modelname;
		}
		elseif (class_exists($modelname)) {
			$model = new $modelname;
		} else {
			throw new Exception("Failed to load $modelname!");
		}
		return $model;
	}	
}


?>