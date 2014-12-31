<?php
/*
 * Created on 12-Oct-06 by Tim Ebenezer
 *
 * RandomSixteenDigit.php
 */
 
 class RandomSixteenChar implements VoucherCodeGeneration {
 	public function execute($seed=null) {
 		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
 		$return = '';
		for ($i=0;$i<16;$i++)
			$return .= $characters[mt_rand(0,strlen($characters)-1)];
		return $return;
 	}
 }
 
?>
