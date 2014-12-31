<?php
/**
 * An extended DOC for working with Taggable things
 * 
 * @author gj
 */
class TaggedItemCollection extends DataObjectCollection {

	/**
	 * 
	 */
	function __construct(Taggable $taggable) {
		parent::__construct($taggable);
	
	}

	public function load(SearchHandler $sh,QueryBuilder $query) {
		$db = DB::Instance();
		$this->query = $query->__toString();

		$perpage = $sh->perpage;
		$c_page = $sh->page;
		
		$rows = $db->GetAssoc($query);
		
		$this->num_records = count($rows);
		$num_records = $this->num_records;
		if($num_records === false) {
			throw new Exception($db->ErrorMsg());
		}
		$this->num_pages = ceil($num_records / max(1, $perpage));
		
		$this->cur_page = $c_page;
		//no need to do anything else if there aren't any rows!
		if($num_records > 0) {
			if (FALSE === $rows) {
				throw new Exception('QUERY FAILED! ' . $db->errormsg());
			}
			$rows = array_slice($rows, $sh->offset, $sh->perpage, true);
			if($rows === false) {
				throw new Exception(
						"TaggedItemCollection load failed: " . $query . $db->ErrorMsg());
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
