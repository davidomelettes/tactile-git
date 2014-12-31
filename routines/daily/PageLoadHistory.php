<?php

/**
 *
 */
class PageLoadHistory extends EGSCLIApplication {

	public function go() {
		$this->logger->info("Calculating page load times");
		$db = DB::Instance();
		//$db->debug();
		/* All we do is tidy up any logged queries from before today into the history table
		 * and then delete them out. We do it in a transaction, just to be safe.
		 */

		$db->StartTrans();
		
		// It removes rogue forums loads/API requests and other pages we aren't bothered about
		$query = "
			DELETE FROM page_load
			WHERE (
				url LIKE '/forums%' OR
				url LIKE '/form_html%' OR
				url LIKE '/welcome%' OR
				url LIKE '/users%' OR
				url LIKE '/?view=%' OR
				url LIKE '//?site_address=%' OR
				url LIKE '%api_token%' OR
				url = '/favicon.ico' OR
				url = '/recent?rss' OR
				url = '/robots.txt'
			)
		";
		
		$db->Execute($query);
		
		// This will work out average load times and insert them into the history table
		$query = "
			INSERT INTO page_load_history
			SELECT
				replace(regexp_replace(
					CASE WHEN url SIMILAR TO '/[a-z]+' THEN url || '/' ELSE url END,
					'[0-9]+/|[^/]+$', ''), '//', '/'
				),
				avg(runtime),
				date_trunc('day', logged)::date
			FROM page_load
			WHERE (
				(url='/') OR 
				(url SIMILAR TO '/[a-z]+/%') OR
				(url SIMILAR TO '//[a-z]+/%') OR
				(url SIMILAR TO '/[a-z]+')
			) AND 
				date_trunc('day', logged) < date_trunc('day', now())
			GROUP BY
				replace(regexp_replace(CASE WHEN url SIMILAR TO '/[a-z]+' THEN url || '/' ELSE url END, '[0-9]+/|[^/]+$', ''), '//', '/'),
				date_trunc('day', logged)
		";
		
		$db->Execute($query);
		
		// This will delete all the old records we have now used
		$query = "
			DELETE FROM page_load
			WHERE (
				(url='/') OR 
				(url SIMILAR TO '/[a-z]+/%') OR
				(url SIMILAR TO '//[a-z]+/%')  OR
				(url SIMILAR TO '/[a-z]+')
			) AND 
				date_trunc('day', logged) < date_trunc('day', now())
		";
		
		$db->Execute($query);
		
		$db->CompleteTrans();
	}
	
}

?>