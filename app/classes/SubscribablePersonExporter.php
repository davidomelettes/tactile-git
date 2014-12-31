<?php

class SubscribablePersonExporter extends PersonExporter {
	
	protected static $returned_fields = array(
		'per.id', 'title', 'firstname', 'surname', 'suffix',
		'can_email', 'email'=>'e.contact'
	);
	
	public function getBy($key = null, $value = null) {
		$db = DB::Instance();
		
		$qb = $this->_exportQuery();
		if (!(is_null($key) || is_null($value))) {
			if (is_bool($value)) {
				$qb->where(new Constraint($key, '=', $value));
			} else {
				$qb->where(new Constraint($key, 'ILIKE', $value));
			}
		}
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('e.contact', 'IS NOT', 'NULL'));
		$cc->add(new Constraint('per.can_email', '=', 'true'));
		$qb->where($cc);
		
		$this->outputRows($qb);
		return $this->getFormatter()->getStream();
	}
	
	function getByTag($tags) {
		if (!is_array($tags)) {
			$tags = array($tags);
		}
		$db = DB::Instance();
		$tag_string = '';
		foreach($tags as $tag) {
			$tag_string .= $db->qstr($tag).',';
		}
		$tag_string = rtrim($tag_string,',');
		
		$model = new Tactile_Person();
		
		$qb = $model->getQueryForTagSearch($tag_string, count($tags));
		
		$fields = self::$_returned_fields;
		$map = array(
			'phone'=>'p.contact',
			'email'=>'e.contact',
			'fax'=>'f.contact'
		);
		foreach($fields as $alias => $field) {
			$fields[$alias] = str_replace('per.', 'ti.', $field);
			if(isset($map[$field])) {
				$fields[$field] = $map[$field];
				unset($fields[$alias]);
			}
		}
		
		$qb->select_simple($fields);
		$qb->group_by($fields);
		$qb->orderby('ti.'.$this->_order, 'ASC');
		
		$this->outputRows($qb);
		return $this->getFormatter()->getStream();
	}
}
