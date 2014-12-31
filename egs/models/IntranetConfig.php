<?php
class IntranetConfig extends DataObject {
	private static $config;
	function __construct() {
		parent::__construct('intranet_config');
		$this->idField='id';
		
		$this->view='';
		
 		$this->belongsTo('IntranetLayout', 'layout_id', 'layout');
		$this->belongsTo('IntranetSection', 'default_section_id', 'default_section'); 

	}

	public static function Fetch() {
		if(self::$config===null) {
			$config = new IntranetConfig();
			$config->loadBy('usercompanyid',EGS_COMPANY_ID);
			self::$config=$config;
		}
		return self::$config;
	}
	
	public function getSingleLevelMenu($section_id=null,$orderby=null) {
		$db = DB::Instance();
		if (empty($section_id)) {
			if (empty($orderby))
				$orderby = 's.position';
			$query = 'select s.id,s.title from intranet_sections s' .
					(!isModuleAdmin()
					?' LEFT JOIN intranet_section_access sa ON (s.id=sa.section_id) '.
						' LEFT JOIN hasrole hr ON (hr.roleid=sa.role_id)'.
						' WHERE (s.owner='.$db->qstr(EGS_USERNAME).' OR hr.username='.$db->qstr(EGS_USERNAME).') AND'
					:' WHERE').
					' s.usercompanyid='.EGS_COMPANY_ID.' order by '.$orderby.' asc';
		}
		else {
			if (empty($orderby))
				$orderby = 'p.menuorder';
			$query = 'select p.id,p.name, r.title, r.revision from intranet_pages p' .
					(!isModuleAdmin()
					?' LEFT JOIN intranet_page_access pa ON (p.id=pa.intranetpage_id) '.
						' LEFT JOIN hasrole hr ON (hr.roleid=pa.role_id)'.
						' LEFT JOIN intranet_page_revisions r ON (p.id=r.intranetpage_id)'.
						' WHERE (p.owner='.$db->qstr(EGS_USERNAME).' OR hr.username='.$db->qstr(EGS_USERNAME).') AND'
					:' LEFT JOIN intranet_page_revisions r ON (p.id=r.intranetpage_id) WHERE').
					' p.usercompanyid='.EGS_COMPANY_ID .
					' AND p.intranetsection_id='.$section_id.
					' AND r.revision=(SELECT max(revision) FROM intranet_page_revisions WHERE intranetpage_id=p.id)'.
					' order by lower('.$orderby.') asc';
			$results = $db->GetAssoc($query);
			$newresults = array();
			foreach ($results as $key=>$value) {
				$page = new IntranetPage();
				$page->load($key);
				$newresults[IntranetPage::buildURL($page)] = $page->revision->title;
			}			
			return $newresults;			
		}
		return $db->GetAssoc($query);
	}
	
	public function getIntranetTree() {
		$tree = new DOMDocument();
		foreach ($this->getSingleLevelMenu() as $id=>$title) {
			$element = $tree->createElement('IntranetSection',$title);
			$element->setAttribute('id',$id);
			$pages = $this->getSectionPages($id,$element,$tree);
			if ($pages)
				$element->appendChild($pages);
			$tree->appendChild($element);
		}
		return $tree;		
	}

	private function getSectionPages($id,$parent,$tree) {
		$db = DB::Instance();
		$query = 'select id, lower(name) from intranet_pages where parent_id is null and intranetsection_id='.$id.' order by menuorder asc';
		$thislevel = $db->GetAssoc($query);
		if ($thislevel) {
			foreach ($thislevel as $pageid=>$name) {
				$element = $tree->createElement('IntranetPage',htmlentities($name,ENT_COMPAT,'UTF-8'));
				$element->setAttribute('id',$pageid);
				$children = $this->getPagesAsTree($pageid,$element,$tree);
				if ($children)
					$element->appendChild($children);
				$parent->appendChild($element);
			}
		}
		else
			return false;
	}
	
	private function getPagesAsTree($id,$parent,$tree) {
		$db = DB::Instance();
		$query = 'select id, lower(name) from intranet_pages where parent_id='.$id.' order by menuorder asc';
		$thislevel = $db->GetAssoc($query);
		if ($thislevel) {
			foreach ($thislevel as $pageid=>$name) {
				$element = $tree->createElement('IntranetPage',$name);
				$element->setAttribute('id',$pageid);
				$children = $this->getPagesAsTree($pageid,$element,$tree);
				if ($children)
					$element->appendChild($children);
				$parent->appendChild($element);
			}
		}
		else
			return false;
	}
	
}
?>
