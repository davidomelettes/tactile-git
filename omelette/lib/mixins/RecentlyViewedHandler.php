<?php
class RecentlyViewedHandler {
	
	function recently_viewed($args) {
		$modelname = $args[0];
		$collection_name = $args[1];
		$model = DataObject::Construct($modelname);
		
		$query = $model->getQueryForRecentlyViewedSearch();
		$collection = new ViewedItemCollection($model);
		$sh = new SearchHandler($collection,false);
		$sh->extractPaging();
		$collection->load($sh,$query);
		
		if (empty($_SERVER['REQUEST_URI'])) {
			// We're in test mode
			$pagination_uri = preg_replace('/\?$/', '', preg_replace('/&?page=\d+/', '', $_GET['url']));
		} else {
			$pagination_uri = preg_replace('/\?$/', '', preg_replace('/&?page=\d+/', '', $_SERVER['REQUEST_URI']));
		}
		$rp = RouteParser::Instance();
		$uri_key = 'pagination_' . $rp->Dispatch('controller') . '_uri';
		$page_key = preg_replace('/_uri$/', '_page', $uri_key);
		Omelette_Magic::saveChoice($uri_key, $pagination_uri);
		Omelette_Magic::saveChoice($page_key, 1);		
		
		$this->view->set('cur_page',$collection->cur_page);
		$this->view->set('num_pages',$collection->num_pages);
		$this->view->set('num_records',$collection->num_records);
		$this->view->set($collection_name,$collection);
		
		$this->setTemplateName('index');
		
		$this->useTagList();
		
		Omelette_Magic::saveChoice($collection_name.'_index_restriction', 'recently_viewed', EGS::getUsername());
		$this->view->set('restriction','recently_viewed');
	}
	
}
?>