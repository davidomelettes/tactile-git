<?php
class IntranetPageFile extends DataObject {
	protected $defaultDisplayFields=array('file');
	

	function __construct() {
		parent::__construct('intranet_page_files');
		$this->idField='id';
		$this->identifierField='file';
		$this->orderby='file';
 		$this->belongsTo('File', 'file_id', 'file');
 		$this->belongsTo('IntranetPage', 'intranetpage_id', 'intranetpage'); 

	}

	public function addLuceneDocument($index) {
		$file =new File(DATA_ROOT.'tmp/');
		$file->load($this->file_id);
		$path=$file->pull();
		
		$doc = new Zend_Search_Lucene_Document();
		
		$url='/?module=intranet&controller=intranetpages&action=viewfile&intranetfile_id='.$this->id;
		
		$doc->addField(Zend_Search_Lucene_Field::Text('url',$url));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('id',$this->id));
		
		
		$contents='';
		switch($file->type) {
			case 'application/msword': {
				$command = 'antiword '.$path['path'].'/'.$path['filename'];
				exec($command,$contents);
				$contents = implode("\n",$contents);		
				break;
			}
			case 'application/pdf': {
				$command = 'pdftotext '.$path['path'].'/'.$path['filename'].' -';
				ob_start();
				exec($command,$contents);
				ob_end_clean();
				$contents = implode("\n",$contents);
				break;
			}
		}
		$contents =htmlentities(strip_tags($contents));
		//echo "Content begins: ".$contents."\n";
		$doc->addField(Zend_Search_Lucene_Field::UnStored('contents',$contents));
		$doc->addField(Zend_Search_Lucene_Field::Text('title',$file->name));
		$index->addDocument($doc);
//		echo "Done\n";
	}
}
?>
