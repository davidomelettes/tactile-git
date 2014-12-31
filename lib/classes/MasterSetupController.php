<?php
class MasterSetupController extends Controller {

	function __construct($module=null,$action=null) {
		parent::__construct($module,$action);
		$this->setup_module=$module;
	}
	
	function index() {
		if (!isModuleAdmin()) {
			$flash = Flash::Instance();
			$flash->addError('You must be a module administrator to access the setup options');
			sendBack();
		}
		$sidebar = new SidebarController($this->view);
		$list=array();
		foreach($this->setup_options as $name=>$modelname) {
			if($modelname=='spacer') {
				$list[]=$modelname;
			}
			else {
				$item=array(
					'tag'=>$name,
					'link'=>array(
						'module' => $this->setup_module,
						'controller'=>'setup',
						'action'=>'view',
						'option'=>$name
					)
				);
				$list[]=$item;
			}
		}
		$sidebar->addList('Options',$list);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		$this->view->set('setup_options',$this->setup_options);		
		$this->setTemplateName('module_setup_index');
	}
	
	function view() {
		isModuleAdmin() or sendBack();
		$this->index();
		$option = $this->checkValidOption() or sendBack();
		$modelname = $this->setup_options[$option];
		$col_name=$modelname.'Collection';
		$model = new $modelname;
		$models = new $col_name;
		$sh = new SearchHandler($models,false);
		$sh->extract();
		$sh->setLimit(0);
		$sh->setOrderBy($model->orderby);
		$models->load($sh);
		$this->view->set('models',$models);
		$this->setTemplateName('module_setup_view');
		$this->view->set('item_identifier',$model->getIdentifier());
		$this->view->set('extrafields',$this->viewExtraFields($option));
		$this->view->set('edit_extrafields',$this->newExtraFields($option));
		if($model->isField('position')||$model->isField('index')) {
			$this->view->set('orderable',true);
		}
	}

	function edit() {
		isModuleAdmin() or sendBack();
		$this->view();
		$option = $this->checkValidOption() or sendBack();
		$model = new $this->setup_options[$option];
		$model->load($this->_data['id']) or sendBack();
		$this->view->set('name_value',$model->{$model->getIdentifier()});
		$extrafields=$this->editExtraFields($option,$model);
		if($model->isField('position')) {
			$_position='position';
		}
		if($model->isField('index')) {
			$_position='index';
		}
		if(!empty($_position)) {
			$position=array('type'=>'hidden','value'=>$model->$_position);
			$extrafields+=array($_position=>$position);
		}
		
		$this->view->set('edit_extrafields',$extrafields);
	}

	function save_item() {
		isModuleAdmin() or sendBack();
		$option = $this->checkValidOption() or sendBack();
		$modelname = $this->setup_options[$option];
		$db=DB::Instance();

		if(isset($this->_data['setup_data'])) {
			$flash=Flash::Instance();
			$option_data = $this->_data['setup_data'];
			$errors=array();
			$model = DataObject::Factory($option_data,$errors,$modelname);
			if($model!==false) {
				if($model->save()!==false) {
					$flash->addMessage('Item added successfully');
					sendBack('success//'.$model->toJSON());
				}
			}
			else {
				$flash->addErrors($errors);
				sendBack('error');
			}
			
		}
		
	}
	
	function delete_items() {
		isModuleAdmin() or sendBack();
		if(isset($this->_data['delete_items'])) {
			$items = $this->_data['delete_items'];
			$this->_data['option']=key($items);
			$option=$this->checkValidOption() or sendBack();
			$modelname = $this->setup_options[$option];
			$model = new $modelname;
			$db=&DB::Instance();
			$db->StartTrans();
			$count=0;
			foreach($items[$option] as $id=>$on) {
				$model->delete($id) && $count++;
			}
			$flash=Flash::Instance();
			if($db->CompleteTrans()) {
				$flash->addMessage($count.' items deleted');
			}
			else {
				$flash->addError('Error deleting items');
			}
		}
		sendBack();
	}
	function setPositions() {
		isModuleAdmin() or die('error1');
		$option = $this->checkValidOption() or die('error2');
		$model = new $this->setup_options[$option];
		if($model->isField('position')) {
			$fieldname='position';
		}
		else if($model->isField('index')) {
			$fieldname='index';
		}
		else {
			die("error3");
		}
		foreach($this->_data['position'] as $position=>$id) {
			$model->update($id,$fieldname,$position+1) or die('error4');
		}
		die('success');
	}
	protected function checkValidOption($value='option') {
		$option = (isset($this->_data[$value]))?$this->_data[$value]:'';
		$valid = isset($this->setup_options[$option]);
		if($valid) {
			return $option;
		}
		$flash = Flash::Instance();
		$flash->addError('Invalid setup option');
		return false;
	}
	protected function newExtraFields($option) {
		return array();
	}
	protected function viewExtraFields($option) {
		return array();
	}
	protected function editExtraFields($option,$model) {
		$fields=array();
		$fields = $this->newExtraFields($option);
		foreach($fields as $name=>$field) {
			$fields[$name]['value']=$model->$name;
		}	
		$fields['id']=array(
			'type'=>'hidden',
			'value'=>$this->_data['id']
		);
		return $fields;
	}
    protected function makeLookupField($modelname,$fieldname,$compulsory=false) {
        $model = new $modelname;
        $options = $model->getAll();
        if(!$compulsory) {
            $options=array(''=>'None')+$options;
        }
        return array(
            'type'=>'select',
            'options'=>$options
        );
    }

	
}
?>