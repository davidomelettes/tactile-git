<?php
/**
 * Mixin-like class for providing methods common to things with tags 
 * (remember $this refers to the calling Controller instance)
 * - add_tag
 * - remove_tag
 * - by_tag - lists items for a particular set of tags
 * Should be fairly self explanatory, relies on TaggedItem to do most of the work
 * 
 * @author gj
 * @package Mixins
 */
class TagHandler {
	
	function add_tag($args) {
		$modelname = $args[0];
		
		$model = DataObject::Construct($modelname);
		$tag = $this->_data['tag'];
		$id = $this->_data['id'];
		
		$user = CurrentlyLoggedInUser::Instance();
		
		$model = $model->load($id);
		if($model===false) {
			Flash::Instance()->addError("Invalid ID specified");
			return;
		} elseif (!$user->canEdit($model)) {
			Flash::Instance()->addError("You do not have permission to edit this item");
			return;
		}
		$taggable = new TaggedItem($model);		
		$success = $taggable->addTag($tag);
		if($success) {
			Flash::Instance()->addMessage('Item tagged successfully');
			$this->view->set('tag',$tag);
			$this->view->set('tagged_item',$taggable);
			return;
		}
		else {
			Flash::Instance()->addError("Error Tagging");
			return;
		}
	}
	
	
	function remove_tag($args) {
		$modelname = $args[0];
		$model = DataObject::Construct($modelname);
		$taggable = new TaggedItem($model);
		
		$user = CurrentlyLoggedInUser::Instance();
		
		if(!isset($this->_data['id']) || false === $model->load($this->_data['id'])) {
			Flash::Instance()->addError("Invalid ID specified");
			return;
		} elseif (!$user->canEdit($model)) {
			Flash::Instance()->addError("You do not have permission to edit this item");
			return;
		}
		if(!empty($this->_data['tag'])) {
			$tag = $this->_data['tag'];
		}
		else {
			Flash::Instance()->addError("Can't remove an empty tag");
			return;
		}
		
		$success = $taggable->removeTagByName($tag);
		if($success) {
			Flash::Instance()->addMessage('Tag removed successfully');
			$this->view->set('tagged_item',$model);
			return;
		}
		else {
			Flash::Instance()->addError("Tag couldn't be removed");
			return;
		}
	}
	
	function by_tag($args) {
		$modelname = $args[0];
		$collection_name = $args[1];
		if(!isset($this->_data['tag'])) {
			$this->_data['tag'] = '';
		}
		$tags = $this->_data['tag'];
		if(!is_array($tags)) {
			$tags = array($tags);
		}
		$taggable = new TaggedItem(DataObject::Construct($modelname));
		$items = $taggable->getCollectionByTags($tags);
		/*//Maybe want to send straight to record if only one match?
		if(count($clients)==1) {
			$client = $clients->getContents(0);
			$id = $client->id;
			sendTo('companys','view','contacts',array('id'=>$id));
			return;
		}
		*/
		$this->view->set('cur_page',$items->cur_page);
		$this->view->set('num_pages',$items->num_pages);
		$this->view->set('num_records',$items->num_records);
		$this->view->set($collection_name,$items);
		$this->view->set('selected_tags',$tags);
		$this->view->set('current_query',http_build_query(array('tag'=>$tags)));
		$this->setTemplateName('index');
		
		if (empty($_GET['limit'])) {
			$limit = Omelette_Magic::getValue('pagination_limit', EGS::getUsername(), 30);
			$this->view->set('perpage', $limit);
		} else {
			$this->view->set('perpage', (int) $_GET['limit']);
		}
		
		$tags_to_show = $taggable->getTagList($tags);
		$this->view->set('all_tags',$tags_to_show);
		
	}
}
