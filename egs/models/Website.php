<?php
class Website extends DataObject {
	protected $defaultDisplayFields=array('name'=>'Name','company'=>'Company','person'=>'Person');
	
	function __construct() {
		parent::__construct('websites');
		$this->idField='id';


 		$this->validateUniquenessOf(array('name','usercompanyid'));
 		$this->belongsTo('Company', 'company_id', 'company');
 		$this->belongsTo('Person', 'person_id', 'person');
 		$this->hasMany('WebpageCategory','webpage_categories');
 		$this->hasMany('Webpage','webpages');
 		$this->hasMany('WebsiteAdmin','admins');
 		$this->hasMany('Newsitem','newsitems');
		$this->hasMany('Poll');
	}

	function isAdmin($username) {
		$db = DB::Instance();
		$query = 'select count(*) from website_admins where username='.$db->qstr($username).' and website_id='.$this->id;
		$result = $db->GetOne($query);
		if ($result > 0)
			return true;
		return false;
	}

	function getSiteTree() {
		if (!$this->_loaded) return false;
		$sitetree= new DOMDocument();
		$this->getPagesAsTree(null,$sitetree,$sitetree);
		$this->getCategoryTree(null,$sitetree,$sitetree);
		if ($this->shop)
			$this->getShopTree(null,$sitetree,$sitetree);
	//	echo $sitetree->saveXML();
	//	exit;
		return $sitetree;
	}

	function getShopTree($id=null,$parent,$sitetree) {
		$db = DB::Instance();
		if (empty($id))
			$query = 'select id, lower(title) from store_sections where parent_id is null and usercompanyid='.$this->company_id;
		else
			$query = 'select id, lower(title) from store_sections where parent_id='.$id.' and usercompanyid='.$this->company_id;
		$thislevel = $db->GetAssoc($query);
		if ($thislevel) {
			foreach ($thislevel as $catid=>$name) {
				$element = $sitetree->createElement('shopsection',$name);
				$element->setAttribute('id',$catid);
				$children = $this->getShopTree($catid,$element,$sitetree);
				if ($children)
					$element->appendChild($children);
				$parent->appendChild($element);
			}
		}
		else
			return false;		
	}

	function getCategoryTree($id=null,$parent,$sitetree) {
		$db = DB::Instance();
		if (empty($id))
			$query = 'select id, lower(name) from webpage_categories where parent_id is null and website_id='.$this->id;
		else
			$query = 'select id, lower(name) from webpage_categories where parent_id='.$id.' and website_id='.$this->id;
		$thislevel = $db->GetAssoc($query);
		if ($thislevel) {
			foreach ($thislevel as $catid=>$name) {
				$element = $sitetree->createElement('webpagecategory',$name);
				$element->setAttribute('id',$catid);
				$children = $this->getCategoryTree($catid,$element,$sitetree);
				$pages = $this->getCategoryPages($catid,$element,$sitetree);
				if ($children)
					$element->appendChild($children);
				if ($pages)
					$element->appendChild($pages);
				$parent->appendChild($element);
			}
		}
		else
			return false;
	}

	function getCategoryPages($id,$parent,$sitetree) {
		$db = DB::Instance();
		$query = 'select id, lower(name) from webpages where parent_id is null and webpage_category_id='.$id.' and website_id='.$this->id . ' order by menuorder asc';
		$thislevel = $db->GetAssoc($query);
		if ($thislevel) {
			foreach ($thislevel as $pageid=>$name) {
				$element = $sitetree->createElement('webpage',$name);
				$element->setAttribute('id',$pageid);
				$children = $this->getPagesAsTree($pageid,$element,$sitetree);
				if ($children)
					$element->appendChild($children);
				$parent->appendChild($element);
			}
		}
		else
			return false;
	}

	function getPagesAsTree($id=null,$parent,$sitetree) {
		$db = DB::Instance();
		if (empty($id))
			$query = 'select id, lower(name) from webpages where parent_id is null and webpage_category_id is null and website_id='.$this->id . ' order by menuorder asc';
		else
			$query = 'select id, lower(name) from webpages where parent_id='.$id.' and webpage_category_id is null and website_id='.$this->id . ' order by menuorder asc';
		$thislevel = $db->GetAssoc($query);
		if ($thislevel) {
			foreach ($thislevel as $pageid=>$name) {
				$element = $sitetree->createElement('webpage',$name);
				$element->setAttribute('id',$pageid);
				$children = $this->getPagesAsTree($pageid,$element,$sitetree);
				if ($children)
					$element->appendChild($children);
				$parent->appendChild($element);
			}
		}
		else
			return false;
	}

	function singleLevelMenu($level=null) {
		$db = DB::Instance();
		if (empty($level)) {
			$query = 'select id from webpages where parent_id is null and webpage_category_id is null and website_id='.$this->id.' and visible order by menuorder asc';
			$results = array();
			foreach ($db->GetCol($query) as $id) {
				$page = new Webpage();
				$page->load($id);
				$results[strtolower($page->name)] = $page->revision->title;
			}
			$query = 'select id from webpage_categories where parent_id is null and website_id='.$this->id.' and visible order by menuorder';
			foreach ($db->GetCol($query) as $id) {
				$page = new WebpageCategory();
				$page->load($id);
				$results[strtolower($page->name)] = $page->title;
			}
			$query = 'select title from store_sections where usercompanyid='.EGS_COMPANY_ID;
			foreach ($db->GetCol($query) as $title) {
				$results['shop/'.strtolower($title)] = $title;
			}			
		}
		else  {
			$sitetree = $this->getSiteTree();
			$id = $db->GetOne("select id from webpage_categories where lower(name) = lower('$level')");
			$query = "select id from webpages where parent_id is null and webpage_category_id=$id and website_id={$this->id} and visible order by menuorder asc";
			$results = array();
			foreach ($db->GetCol($query) as $id) {
				$page = new Webpage();
				$page->load($id);
				$results[strtolower($page->name)] = $page->revision->title;
			}
		}
		return $results;
	}

	function getAllFiles() {
		$db = DB::Instance();
		$query = 'select file_id from website_files where website_id='.$this->id;
		$files = $db->GetCol($query);
		if ($files) {	
			foreach($files as $file) {
				$fileObj = new File(FILE_ROOT.'data/tmp/');
				$fileObj->load($file);
				$fileObj->Pull();
			}
		}
	}
}
?>
