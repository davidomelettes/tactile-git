<?php

class InvoiceNumberHandler extends AutoHandler {

	function handle(DataObject $model) {
	$jn=$model->job_no;
	if(empty($jn)) {
		$db=DB::Instance();
		$query='SELECT max(invoice_number) FROM '.$model->getTableName().' WHERE usercompanyid='.EGS_COMPANY_ID;
		$current=$db->GetOne($query);
		return $current+1;
	}
	}
}
?>
