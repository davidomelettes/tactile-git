<?php
/**
 * Wrapper for DataObjects that want to do things with tags. 
 * - add a tag to an item by providing the tag-name, will either re-use or create as appropriate
 * - remove a tag from an item by providing the tag-name
 * - gets items of the type that have one or more specified tags
 * - gets a list of tags that is a subset of a (possibly empty) set of 'current tags'- for drilling-down
 * 
 * @author gj
 */
class TaggedItem {
	
	/**
	 * The Taggable DataObject
	 *
	 * @var Taggable
	 */
	protected $taggable;
	
	/**
	 * The foreign key field in tag_map that's used
	 *
	 * @var String
	 */
	protected $taggable_fkey;
	
	/**
	 * Decorate a DataObject with functions that do tag-stuff
	 * The DO must implement Taggable, which specifies some functions to return queries
	 *
	 * @param Taggable $taggable
	 */
	public function __construct(Taggable $taggable) {
		$this->taggable = $taggable;
		$this->taggable_fkey = strtolower($taggable->get_name()).'_id';
	}
	
	
	/**
	 * Returns an array of all tags attached to the item
	 * @return Array
	 */
	public function getTags() {
		$db = DB::Instance();
		$query = 'SELECT t.name AS tag FROM tags t JOIN tag_map tm ON (t.id=tm.tag_id)
			WHERE tm.'.$this->taggable_fkey.'='.$db->qstr($this->taggable->id).' AND t.usercompanyid='.$db->qstr(EGS::getCompanyId()).'
			ORDER BY tm.created DESC';
		$tags = $db->GetCol($query);
		return $tags;
	}
	
	/**
	 * Add a tag to a taggable item
	 * - re-uses tags if they exist, otherwise adds a new one
	 * @param String $tag_name
	 * @return Boolean
	 */
	public function addTag($tag_name) {
		if ($tag_name instanceof Omelette_Tag) {
			$tag = $tag_name;
			$exists = true;
			$tag_name = $tag->name;
		} else {
			$tag = new Omelette_Tag();
			$exists = $tag->loadBy('name',$tag_name); 
		}
		
		$db = DB::Instance();
		$errors=array();
		if($exists==false) {
			$tag = DataObject::Factory(array('name'=>$tag_name),$errors,'Omelette_Tag');
			if($tag===false || $tag->save()===false) {
				Flash::Instance()->addErrors($errors);
				return false;
			}
		}
		elseif($this->hasTag($tag)) {
			Flash::Instance()->addError('Item already has that tag','tag');
			return false;
		}
		$query = 'INSERT INTO tag_map (tag_id,'.$this->taggable_fkey.', hash) 
			VALUES ('.$db->qstr($tag->id).','.$db->qstr($this->taggable->id).', ' . $db->qstr(substr($this->taggable_fkey,0,3).$this->taggable->id).')';
		return $db->Execute($query) !== false;
	}
	
	/**
	 * Returns true iff the item has the given tag attached to it
	 *
	 * @param Omelette_Tag $tag
	 * @return Boolean
	 */
	public function hasTag($tag) {
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM tag_map WHERE '.$this->taggable_fkey.'='.$db->qstr($this->taggable->id).' AND tag_id='.$db->qstr($tag->id);
		return $db->getOne($query)>0;
	}
	
	/**
	 * Returns a DOC containing all items of the type that match *all* of the supplied tags
	 * @param Array $tags
	 * @return DataObjectCollection
	 */
	public function getCollectionByTags($tags) {
		$db = DB::Instance();
		
		$tag_string = '';
		foreach($tags as $tag) {
			$tag_string .= $db->qstr($tag).',';
		}
		$tag_string = rtrim($tag_string,',');
		
		$query = $this->taggable->getQueryForTagSearch($tag_string,count($tags));
		$collection = new TaggedItemCollection($this->taggable);
		$sh = new SearchHandler($collection,false);
		$limit = 30;
		if (empty($_GET['limit'])) {
			$limit = Omelette_Magic::getValue('pagination_limit', EGS::getUsername(), 30);
		}
		$sh->extractPaging(1, $limit);
		$collection->load($sh,$query);		
		
		return $collection;
	}
	
	/**
	 * DELETE all items of this type, which match the tag string
	 *
	 * @param array $tags
	 * @return ?
	 */
	public function deleteAllByTags($tags) {
		$db = DB::Instance();
		
		$tag_string = '';
		foreach($tags as $tag) {
			$tag_string .= $db->qstr($tag).',';
		}
		$tag_string = rtrim($tag_string,',');
		
		$query = $this->taggable->getQueryForTagDeletion($tag_string,count($tags));
		
		$success = $db->execute($query->__toString());
		
		return $success;
	}
	
	/**
	 * Selects all tags that have a tagged-item (of the same type) in common with all of the provided list
	 *
	 * @param Array $tags
	 * @return Array
	 */
	public function getRestrictedTagList($tags) {
		$db = DB::Instance();
		$tag_string = '';
		foreach($tags as $tag) {
			$tag_string .= $db->qstr($tag).',';
		}
		$tag_string = rtrim($tag_string,',');
		$query = $this->taggable->getQueryForRestrictedTagList($tag_string,count($tags));
		$tags = $db->GetCol($query);
		return $tags;
	}
	
	/**
	 * Return an array of all tags that are attached to at least one of the item in question
	 * @return Array
	 */
	public function getFullTagList() {
		$db = DB::Instance();
		$query = $this->taggable->getQueryForFullTagList();
		$tags = $db->GetCol($query);
		if($tags===false) {
			die($db->ErrorMsg());
		}
		return $tags;
	}
	
	/**
	 * Return a list of tags suitable for displaying
	 * if $tags is empty, then all tags will be shown, if not then the list will be restricted
	 *
	 * @param Array optional $tags
	 * @return Array
	 */
	public function getTagList($tags=null) {
		if($tags==null) {
			return $this->getFullTagList();
		}
		return $this->getRestrictedTagList($tags);
	}
	
	/**
	 * Takes a tag off an entry
	 * @param String $name
	 */
	public function removeTagByName($name) {
		$tag = new Omelette_Tag();
		
		$tag = $tag->loadBy('name',$name);
		if($tag===false) {
			return false;
		}
		$db = DB::Instance();
		$query = 'DELETE FROM tag_map WHERE tag_id='.$db->qstr($tag->id).' AND '.$this->taggable_fkey.'='.$db->qstr($this->taggable->id);
		$rs = $db->Execute($query);

		$success = $rs!==false && $db->Affected_Rows() > 0 && Omelette_Tag::cleanOrphans($tag);
		return $success;
	}
	
	/**
	 * Takes an array of tagnames and an array of taggable ids and applies each tag to each taggable
	 *
	 * @param Array $tags
	 * @param Array $ids
	 */
	public function addTagsInBulk($tags,$ids) {
		$db = DB::Instance();
		foreach($tags as $name) {
			$tag = Omelette_Tag::loadOrCreate($name);
			if($tag!==false) {
				$query = 'INSERT INTO tag_map (tag_id,'.$this->taggable_fkey.', hash) ('.
					'SELECT '.$db->qstr($tag->id).',id,' . $db->qstr(substr($this->taggable_fkey,0,3)).' || id
					  FROM '.$this->taggable->getTableName().
					' WHERE id IN ('.implode(',',$ids).'))';
				$db->Execute($query);
			}
		}
	}
}
?>