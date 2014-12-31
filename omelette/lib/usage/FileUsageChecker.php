<?php

/**
 * Responsible for checking the total size of files owned by the account
 * 
 * @author gj
 */
class FileUsageChecker extends UsageCheckerAbstract {

	/**
	 * 
	 * @see UsageCheckerAbstract::getUsage()
	 */
	public function calculateUsage() {
		return S3File::getUsage($this->account->organisation_id);
	}
	
	/**
	 * Returns the file-size with KB, MB, GB units as appropriate (using the FilesizeFormatter)
	 *
	 * @return String
	 */
	public function getFormattedUsage() {
		$formatter = new FilesizeFormatter();
		return $formatter->format($this->getUsage());
	}
}

?>