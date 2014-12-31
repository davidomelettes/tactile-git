<?php

/**
 * Responsible for representing a record of a user viewing a particular entity.
 * 
 * @author gj
 */
class ViewedPage {

	const TYPE_ORGANISATION = 'organisations';
	
	const TYPE_PERSON = 'people';
	
	const TYPE_ACTIVITY = 'activities';
	
	const TYPE_OPPORTUNITY = 'opportunities';
	
	protected static $singular_types = array(
		self::TYPE_ORGANISATION => 'organisation',
		self::TYPE_PERSON => 'person',
		self::TYPE_ACTIVITY => 'activity',
		self::TYPE_OPPORTUNITY => 'opportunity'
	);
	
	/**
	 * The name of the table that is logged to
	 * 
	 * @static String
	 */
	protected static $TABLE_NAME = 'recently_viewed';

	/**
	 * The entry's id
	 *
	 * @var Integer
	 */
	protected $id;

	/**
	 * The entry's type
	 * -one of 'clients', 'people', 'opportunities', 'activities'
	 * 
	 * @var String
	 */
	protected $type;

	/**
	 * The entry's link-id (the fkey to the other table)
	 *
	 * @var Integer
	 */
	protected $link_id;

	/**
	 * The entry's owner (a username)
	 *
	 * @var String
	 */
	protected $owner;

	/**
	 * A label given to the entity (it's "name"), used when listing
	 * 
	 * @var String
	 */
	protected $label;
	
	/**
	 * The timestamp of the last change
	 *
	 * @var String
	 */
	protected $created;

	/**
	 * Construct a ViewedPage by providing it with the properties that define it
	 * 
	 * @param String $type The type of entity
	 * @param Int $link_id The id of the entity being viewed
	 * @param String $owner The full username
	 * @param Int optional $id The id of the ViewedPage itself (when updating)
	 */
	public function __construct($type, $link_id, $owner, $id=null) {
		$this->type = $type;
		$this->link_id = $link_id;
		$this->owner = $owner;
		if(isset($id)) {
			$this->id = $id;
		}
	}

	/**
	 * Setter for the 'label' property
	 * 
	 * @param String $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}
	
	/**
	 * Getter for the 'label' property (everything else is part of the URL)
	 *
	 * @return String
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * Saves the page-view to the database
	 * 
	 * @return void
	 */
	public function save() {
		$page_data = array(
			'owner'=>$this->owner,
			'label'=>$this->label,
			'type'=>$this->type,
			'link_id'=>$this->link_id,
			'created'=>'now()'
		);
		$fk = $this->getType() . '_id';
		$page_data[$fk] = $this->link_id;
		if(!is_null($this->id)) {
			$page_data['id'] = $this->id;
		}
		$db = DB::Instance();
		return $db->Replace(self::$TABLE_NAME,$page_data,'id',true);
	}
	
	/**
	 * Load a ViewedPage entry based on the (type,link_id,owner) tuple
	 * - returns false if there's no matching entry
	 * 
	 * @param String $type
	 * @param Int $link_id
	 * @param String $owner
	 * @return ViewedPage|Boolean
	 */
	public static function Load($type, $link_id, $owner) {
		$db = DB::Instance();
		$query = new QueryBuilder($db);
		$cc = new ConstraintChain();
		$cc->add(new Constraint('type','=',$type));
		$cc->add(new Constraint('link_id','=',$link_id));
		$cc->add(new Constraint('owner','=',$owner));
		
		$query->select_simple(array('id'))
			->from(self::$TABLE_NAME)
			->where($cc);
		$id = $db->GetOne($query->__toString());
		if($id===false) {
			return false;
		}
		$view = new ViewedPage($type,$link_id,$owner,$id);
		return $view;
	}
	
	/**
	 * Make a record of a user viewing a page, either by updating an existing record or by creating a new one, as appropriate
	 *
	 * @param String $type
	 * @param Int $link_id
	 * @param String $owner
	 */
	public static function createOrUpdate($type,$link_id,$owner,$label) {
		$view = ViewedPage::Load($type,$link_id,$owner);
		if($view===false) {
			$view = new ViewedPage($type,$link_id,$owner);
		}
		$view->setLabel($label);
		$view->save();
	}
	
	/**
	 * Returns an array of ViewedPage items with as many entries as the supplied $length (or fewer)
	 *
	 * @param Integer $length
	 * @return Array
	 */
	public static function getList($length) {
		$db = DB::Instance();
		$query = new QueryBuilder($db);
		$query->select_simple(array('*'))
			->from(self::$TABLE_NAME)
			->where('owner='.$db->qstr(EGS::getUsername())) 
			->orderby('created','desc')
			->limit($length);
		$rows = $db->GetArray($query->__toString());
		$pages = array();
		foreach($rows as $row) {
			$pages[] = self::CreateFromArray($row);
		}
		return $pages;
	}
	
	/**
	 * Helper method for creating a ViewedPage from an associative array- from a DB query for example
	 * - constructs a ViewedPage, and then calls setLabel()
	 *
	 * @param Array $array
	 * @return ViewedPage
	 */
	public static function CreateFromArray($array) {
		$page = new ViewedPage($array['type'], $array['link_id'], $array['owner'], $array['id']);
		$page->setLabel($array['label']);
		return $page;
	}
	
	/**
	 * Returns a string containing the url to view the entity
	 *
	 * @return String
	 */
	public function getURL() {
		return '/'.$this->type.'/view/'.$this->link_id;
	}
	
	/**
	 * Returns the singular form of the item's type
	 *
	 * @return String
	 */
	public function getType() {
		return self::$singular_types[$this->type];
	}

}

?>
