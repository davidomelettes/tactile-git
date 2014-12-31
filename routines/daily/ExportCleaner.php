<?php

class ExportCleaner extends EGSCLIApplication {
	
	public function go() {

		$path = DATA_ROOT . 'exports/';
		$this->logger->info("Scanning export directory: $path");
		
		if (FALSE === ($dh = opendir($path))) {
			$this->logger->warn("Could not open export directory for reading! $path");
			exit(1);
		}
		$files = 0;
		$files_deleted = 0;
		$total_size = 0;
		$strange_files = 0;
		while (false !== ($file = readdir($dh))) {
			if (!preg_match('/^\./', $file)) {
				if (preg_match('/[^\d]+_(\d+)_(\d+)\.zip/', $file, $matches)) {
					$account_id = $matches[1];
					$timestamp = $matches[2];
					if (strtotime('- 7 days') > $timestamp) {
						// Too old, delete
						if (!unlink($path . $file)) {
							$this->logger->warn("Failed to delete file: $path$file");
							$files++;
							$total_size += filesize($path . $file);
						} else {
							$files_deleted++;
						}
					} else {
						$files++;
						$total_size += filesize($path . $file);
					}
				} else {
					$strange_files++;
				}
			}
		}
		$this->logger->info("$files_deleted files deleted, $files remaining (total size: $total_size)");
		if ($strange_files) {
			$this->logger->info("$strange_files strange files detected");
		}
	}
	
}
