<?php

class CSVExportFormatter extends ExportFormatter {
	
	public function addHeadings() {
		fputcsv($this->_stream, $this->_ordering);
	}
	
	public function output($rows) {
		foreach($rows as $row) {
			$csv_line = array();
			foreach ($this->_ordering as $i => $fieldname) {
				$value = isset($row[$fieldname]) ? $row[$fieldname] : '';
				if (is_array($value)) {
					$temp_stream = fopen('php://temp/maxmemory:5000', 'w+');
					fputcsv($temp_stream, $value, '|');
					rewind($temp_stream);
					$value = trim(stream_get_contents($temp_stream));
					fclose($temp_stream);
				}
				$csv_line[$i] =  $value;
			}
			$success = fputcsv($this->_stream, $csv_line);
			if ($success === FALSE) {
				throw new Exception('Failed to write CSV line to stream!');
			}
		}
	}
	
}
