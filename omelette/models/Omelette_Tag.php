<?php
/**
 *
 * @author gj
 */
class Omelette_Tag extends DataObject {
	
	public function __construct() {
		parent::__construct('tags');
		$this->orderby = 'created';
		$this->orderdir = 'DESC';
	}
	
	/**
	 * Removes all tags that aren't currently attached to anything
	 * - this should be called when an entry in tag_map is deleted
	 * @return Boolean
	 */
	public static function cleanOrphans($tag = null) {
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM tag_map WHERE tag_id = ' . $db->qstr($tag->id);
		$count = $db->getOne($query);
		if($count == 0) {
			return $tag->delete();
		}
		return true;
	}
	
	/**
	 * Convenience function for getting a tag based on its name, creating it if it doesn't exist
	 * 
	 * @param String $tag_name
	 * @return Omelette_Tag
	 */
	public static function loadOrCreate($tag_name) {
		$tag = new Omelette_Tag();
		$exists = $tag->loadBy('name',$tag_name);
		$errors=array();
		if($exists==false) {
			$tag = DataObject::Factory(array('name'=>$tag_name),$errors,'Omelette_Tag');
			if($tag===false || $tag->save()===false) {
				return false;
			}
		}
		return $tag;
	}
	
	/**
	 * Merge with a different tag, then remove
	 *
	 * @param Omelette_Tag $winner
	 */
	public function mergeWith($winner) {
		$db = DB::Instance();
		$db->StartTrans();
		
		//need to delete any that would result in duplicates
		$query = 'DELETE FROM tag_map WHERE id IN (
			select a.id from tag_map a join tag_map b on (a.hash=b.hash) 
			where a.tag_id = ' . $db->qstr($this->id) . ' AND b.tag_id = ' . $db->qstr($winner->id) . ')';
		
		$success = $db->Execute($query);
		if($success === false) {
			$db->failTrans();
			$db->CompleteTrans();
			echo $query;
			throw new Exception("Database error: " .  $db->errorMsg());
		}
		
		//then transfer all the old-tagged things to be the new tag
		$query = 'UPDATE tag_map SET tag_id = ' . $db->qstr($winner->id) 
			. ' WHERE tag_id = ' . $db->qstr($this->id);
		$success = $db->Execute($query);
		if($success === false) {
			$db->failTrans();
			$db->CompleteTrans();
			throw new Exception("Database error: " .  $db->errorMsg());
		}
		
		//and then delete the tag itself
		$success = $this->delete();
		if($success === false) {
			$db->failTrans();
			$db->CompleteTrans();
			throw new Exception("Database error: " .  $db->errorMsg());
		}
		return $db->CompleteTrans();
	}
}
?>