<?php

class Newsitem extends DataObject {
	protected $defaultDisplayFields=array('headline'=>'Headline','teaser'=>'Teaser','publishon'=>'Publish On');
	function __construct() {
		parent::__construct('news_items');
		$this->orderby = 'publishon';
		$this->orderdir = 'desc';
		$this->belongsTo('Webpage','webpage_id','webpage');
		$this->belongsTo('WebpageCategory','category_id','category');
	}
	
	function permalink() {
		$date = explode('/',$this->publishon);
		if(count($date)==3) {
			$year = $date[2];
			$month = $date[1];
			$day = $date[0];
			$date = $year.'/'.$month.'/'.$day;
		}
		else {
			$date=str_replace('-','/',$this->publishon);
		}
		
		return '/news/'.$date.'/'.$this->id.'/';
	}
	
	public static function getArchive($where) {
		$db=DB::Instance();
		$query = 'SELECT DISTINCT extract(year from publishon) AS year, extract(month from publishon) AS month, count(id) AS count FROM news_items i WHERE ';
		$query.= $where;
		$query.=' GROUP BY extract(year from publishon) , extract(month from publishon) ORDER BY extract(year from publishon) DESC, extract(month from publishon) DESC';
		$dates = $db->GetArray($query);

		$archive=array();
		
		foreach($dates as $month_data) {
			if(!isset($archive[$month_data['year']])) {
				$archive[$month_data['year']]=array();
			}
			$archive[$month_data['year']][$month_data['month']]=$month_data['count'];
		}
		return $archive;
	}
}

?>
