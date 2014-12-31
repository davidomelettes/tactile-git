<?php

/**
 *
 */
class TablePruner extends EGSCLIApplication {

	public function go() {
		$this->logger->info("Pruning Tables");
		$db = DB::Instance();
		//$db->debug();
		// All we do is remove any items from recently_viewed over 2 months old

		$db->StartTrans();
		
		$this->logger->info("Pruning recently viewed items over 2 months old");
		$query = "
			DELETE FROM recently_viewed where created <= now() - interval '2 months'	
		";
		
		$db->Execute($query);
		
		$db->CompleteTrans();
	}
	
}

?>
