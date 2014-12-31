<?php
class Webpage extends DataObject {
	protected
$defaultDisplayFields=array('name','description','webpage_category');
	function __construct() {
		parent::__construct('webpages');
		$this->idField='id';

 		$this->validateUniquenessOf(array('name','website_id','parent_id'));
 		$this->belongsTo('WebpageCategory', 'webpage_category_id', 'webpage_category');
		$this->belongsTo('Website','website_id','website');
		$this->belongsTo('Webpage','parent_id','parent');
		//$this->hasMany('Webpage','webpages','parent_id');
		$this->hasMany('WebpageRevision','revisions');
		$this->actsAsTree();		
		$sh = new SearchHandler(new WebpageRevisionCollection,FALSE);
		$sh->setOrderby('revision','desc');
		$this->setAlias('revision','WebpageRevision',$sh,'title');
		
	}
	
	function hasChildren() {
		return (count($this->webpages)>0);
	}
	
	function getSubPages($visible=false) {
		$pages = new WebpageCollection();
		$sh=new SearchHandler($pages,false);
		$sh->addConstraint(new Constraint('parent_id','=',$this->id));
		if($visible) {
			$sh->addConstraint(new Constraint('visible','=','true'));
		}
		$sh->setOrderby('menuorder');
		$pages->load($sh);
		return $pages;
	}

	function getCategory() {
		$cat=$this->webpage_category;
		$parent_id=$this->getParent();
		
		while(empty($cat)&&!empty($parent_id)) {
			$parent=new Webpage();
			$parent->load($parent_id);
			$cat=$parent->webpage_category;
			$parent_id=$parent->getParent();
		}
		return $cat;
	}
	function getAlphaPage() {
		$cat=$this->webpage_category_id;
		if(!empty($cat)) {
			return $this;
		}
		$parent_id=$this->parent_id;
		if (empty($parent_id))
			return $this;
		while(empty($cat)&&!empty($parent_id)) {
			$parent=new Webpage();
			$parent->load($parent_id);
			$cat=$parent->webpage_category_id;
			$parent_id=$parent->getParent();
		}
		return $parent;
	}
	function getAlphaSubPages($visible=false) {
		return $this->getAlphaPage()->getSubPages($visible);
	}
	function isCurrent($name) {
		if(strtolower($this->name)==strtolower($name))
			return true;
		$page=new Webpage();
		$chain = new ConstraintChain();
		$chain->add(new Constraint('lower(name)','=',$name));
		$chain->add(new Constraint('website_id','=',WEBSITE_ID));
		$page=$page->loadBy($chain);
		if($page==false)
			return false;
		foreach($page->getSubPages() as $subpage) {
			if(strtolower($subpage->name)==strtolower($this->name)) {
				return true;
			}
		}
		return false;
	}

}
?>
