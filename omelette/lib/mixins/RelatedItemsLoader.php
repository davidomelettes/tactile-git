<?php
/**
  This handles the loading of related items (in the sidebar) for 'things'.
  It's part of the pseudo-mixin stuff, so (confusingly) $this in the function references the _calling_ class, i.e. the controller, but you can't access private things
*/
class RelatedItemsLoader {

	function get_related($args) {
		$name=$args[0];
		$modelname=$args[1];
		$model = DataObject::Construct($modelname);
		$model->_data=array('id'=>$this->_data['id']);
		$model->load($this->_data['id']);
		switch($name) {
			case 'people': {
				$related = new Omelette_PersonCollection();
				$sh = new SearchHandler($related,false);
				$sh->addConstraint(new Constraint('per.'.strtolower($modelname).'_id', '=', $this->_data['id']));
				$related->load($sh);
				$this->view->set($name,$related);
				break;
			}
			case 'activities': {
				$activities = new Tactile_ActivityCollection();
				$sh = new SearchHandler($activities,false);
				$sh->setOrderBy('act.due', 'ASC');
				$sh->addConstraint(new Constraint('act.'.strtolower($modelname).'_id', '=', $this->_data['id']));
				$sh->addConstraint(new Constraint('completed','IS','NULL'));
				$model->addSearchHandler($name,$sh);
				
				$activities->load($sh);
				$this->view->set('overdue_activities', new OverdueFilter($activities));
				$this->view->set('due_activities', new TodayFilter($activities));
				$this->view->set('upcoming_activities', new LimitIterator(new FutureFilter($activities),0,10));
				$this->view->set('later_activities', new LaterFilter($activities));
				break;
			}
			case 'opportunities': {
				$opportunities = new Tactile_OpportunityCollection();
				$sh = new SearchHandler($opportunities,false);
				$sh->addConstraint(new Constraint('archived','=', false));
				$model->addSearchHandler($name,$sh);
				$related = $model->$name;
				$this->view->set($name,$related);
				break;
			}
			case 's3_files': {
				$files = new S3FileCollection();
				$sh = new SearchHandler($files, false);
				$sh->addConstraint(new Constraint('bucket', '!=', S3_PUBLIC_BUCKET));
				$sh->addConstraint(new Constraint('email_id', 'IS', 'NULL'));
				$model->addSearchHandler($name, $sh);
				$related = $model->$name;
				$this->view->set($name,$related);
				break;
			}
			default:
				$related = $model->$name;
				$this->view->set($name,$related);
		}
		
		$this->setTemplateName($name);
		$this->view->set('attached_to', $modelname);
		$this->view->set('attached_id', $model->id);
	}
}
