<?php
class ViewedItemCollection extends DataObjectCollection {
	
	function __construct($model) {
		parent::__construct($model);
	}
	
	public function load(SearchHandler $sh,QueryBuilder $query) {
		$db = DB::Instance();
		$this->query = $query->orderby('rv.created', 'DESC')->limit($sh->perpage, 0)->__toString();
		
		$perpage = 30;//$sh->perpage;
		$c_page = $sh->page;
		
		$num_records = 30;
		$this->num_records = $num_records;
		if($num_records === false) {
			throw new Exception($db->ErrorMsg());
		}
		$this->num_pages = ceil($num_records / max(1, $perpage));
		
		$this->cur_page = $c_page;
		//no need to do anything else if there aren't any rows!
		if($num_records > 0) {
			$rows = $db->GetAssoc($query);
			if($rows === false) {
				debug_print_backtrace();
				throw new Exception(
						"ViewedItemCollection load failed: " . $query . $db->ErrorMsg());
			}
			if($sh instanceof SearchHandler) {
				$sh->save();
			}
			foreach($rows as $id=>$row) {
				$do = clone $this->_templateobject;
				
				$row[$do->idField] = $id;
				$do->_data = $row;
				$do->load($id);
				
				$this->_dataobjects[] = $this->copy($do);
				$this->data[] = $row;
			}
		}
	}
	
}
?>