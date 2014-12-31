<?php
/**
* Ensures that there will be a 'main' contact method if the save happens
*
*/
class ContactMethodHandler extends AutoHandler {
	
	public function __construct($f_key) {
		$this->f_key=$f_key;
		parent::__construct();
	}
	
	function handle(DataObject $model) {
		$type = $model->type;
		$f_value = $model->{$this->f_key};
		if(empty($type)||empty($f_value)) {
			return false;
		}
		$classname = $model->get_name();
		$model2 = new $classname;
		$cc = new ConstraintChain();
		$cc->add(new Constraint($this->f_key,'=',$f_value));
		$cc->add(new Constraint('type','=',$type));
		$cc->add(new Constraint('main','=','true'));
		$result = $model2->loadBy($cc);
		return ($result===false);
	}
}
?>