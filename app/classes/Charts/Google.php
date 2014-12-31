<?php

/**
 * Ancestor abstract for all Google Charts
 *
 * @author de
 */
abstract class Charts_Google {
	
	const MAX_PIXELS = 300000; // http://code.google.com/apis/chart/basics.html#chart_size
	
	protected static $_unsecure_url = 'http://chart.apis.google.com/chart?';
	protected static $_secure_url = '/chart.php?';
	protected static $_translated_markers = array(
		'arrow'		=> 'a',
		'cross'		=> 'c',
		'diamond'	=> 'd',
		'circle'	=> 'o',
		'square'	=> 's',
		'text'		=> 't',
		'vertical'	=> 'v',
		'Vertical'	=> 'V',
		'horizontal'=> 'h',
		'x'			=> 'x',
		'range'		=> 'r',
		'Range'		=> 'R',
		'fill'		=> 'b'
	);
	protected static $_translated_fills = array(
		'background'=> 'bg',
		'chart'		=> 'c'
	);
	
	protected $_type = '';
	
	protected $_sizeX = 0;
	protected $_sizeY = 0;
	protected $_data = array();
	protected $_dataType = array();
	
	protected $_axes = array();
	
	protected $_singleOptions = array();
	protected $_commaOptions = array();
	protected $_pipeOptions = array();
	protected $_commaPipeOptions = array();
	
	
	public function addDataSet($data, $dataType='t') {
		switch ($dataType) {
			case 't':
				$this->addTextData($data);
				break;
			case 's':
				$this->addSimpleEncodingData($data);
				break;
			case 'e':
				$this->addExtendedEncodingData($data);
				break;
			default:
				throw new Exception('DataType must be one of: t, s, e');
		}
		
		return $this;
	}
	
	public function setSize($sizeX, $sizeY) {
		$area = (int)$sizeX * (int)$sizeY; 
		if ($area < 1 || $area > self::MAX_PIXELS) {
			throw new Exception('Area must be between 1 and ' . self::MAX_PIXELS . ' pixels');
		}
		$this->_sizeX = $sizeX;
		$this->_sizeY = $sizeY;
		
		return $this;
	}
	
	protected function _setSingleOption($key, $option) {
		$this->_singleOptions[$key] = $option;
	}
	
	protected function _setCommaOptions($key, $options) {
		if (!is_array($options)) {
			$options = array($options);
		}
		$this->_commaOptions[$key] = $options;
	}
	
	protected function _addCommaPipeOptions($key, $options) {
		if (!is_array($options)) {
			$options = array($options);
		}
		$this->_commaPipeOptions[$key][] = $options;
	}
	
	protected function _setPipeOptions($key, $options) {
		if (!is_array($options)) {
			$options = array($options);
		}
		$this->_pipeOptions[$key] = $options;
	}
	
	public function addAxis($axis, $labels = array()) {
		if (!is_array($labels)) {
			throw new Exception('Second argument must be an array of labels');
		}
		switch ($axis) {
			case 'x':
			case 'y':
			case 'r':
			case 't':
				$this->_axes[$axis][] = $labels;
				break;
			default:
				throw new Exception('First argument must be one of: x, y, r, t');
				break;	
		}
		
		return $this;
	}
	
	public function addAxisStyle($style) {
		$this->_addCommaPipeOptions('chxs', $style);
		
		return $this;
	}
	
	public function addTextData($data) {
		if (!is_array($data)) {
			throw new Exception('Data must be provided as an array of values');
		}
		$this->_dataType[] = 't';
		$this->_data[] = $data;
		
		return $this;
	}
	
	public function addSimpleEncodingData($data) {
		if (!is_array($data)) {
			throw new Exception('Data must be provided as an array of values');
		}
		$this->_dataType[] = 's';
		$this->_data[] = $data;
		
		return $this;
	}
	
	public function addExtendedEncodingData($data) {
		if (!is_array($data)) {
			throw new Exception('Data must be provided as an array of values');
		}
		$this->_dataType[] = 'e';
		$this->_data[] = $data;
		
		return $this;
	}
	
	public function addDataScaling($scaling) {
		$this->_setCommaOptions('chds', $scaling);
		
		return $this;
	}
	
	public function setTitle($title) {
		$this->_setSingleOption('chtt', $title);
		
		return $this;
	}
	
	public function setTitleStyle($style) {
		$this->_setCommaOptions('chts', $style);
		
		return $this;
	}
	
	public function setLegend($labels) {
		$this->_setCommaOptions('chdl', $labels);
		
		return $this;
	}
	
	public function setLegendPosition($position) {
		switch ($position) {
			case 'b':
			case 't':
			case 'r':
			case 'l':
				break;
			default:
				throw new Exception('Position must be one of: b, t, r, l');
				break;
		}
		$this->_setSingleOption('chdlp', $position);
		
		return $this;
	}
	
	public function setColours($colours) {
		$this->_setCommaOptions('chco', $colours);
		
		return $this;
	}
	
	public function setGrid($grid) {
		$this->_setCommaOptions('chg', $grid);
		
		return $this;
	}
	
	public function addMarkers($options) {
		if (!in_array($options[0], array_values(self::$_translated_markers))) {
			if (!isset(self::$_translated_markers[$options[0]])) {
				throw new Exception('Marker type must be one of: ' . implode(array_values(self::$_translated_markers), ', '));
			}
			$options[0] = self::$_translated_markers[$options[0]];
		}
		$this->_addCommaPipeOptions('chm', $options);
		
		return $this;
	}
	
	public function addFill($style) {
		$this->_addCommaPipeOptions('chf', $style);
		
		return $this;
	}
	
	protected function _countData() {
		$data = $this->_data[0];
		
		return count($data);
	}
	
	public function outputSrc($sizeX, $sizeY) {
		$this->setSize($sizeX, $sizeY);
		
		$output = isset($_SERVER['HTTP_X_FARM']) ? self::$_secure_url : self::$_unsecure_url;
		
		$args = array();
		$args['cht'] = $this->_type;
		$args['chs'] = $this->_sizeX . 'x' . $this->_sizeY;
		$datas = array();
		for ($i = 0; $i < count($this->_data); $i++) {
			$datas[] = implode($this->_data[$i], ',');
		}
		// Can't mix data types, so use the type of the first set
		$args['chd'] = $this->_dataType[0] . ':' . implode('|', $datas);
		
		foreach ($this->_singleOptions as $k => $v) {
			$args[$k] = $v;
		}
		
		foreach ($this->_commaOptions as $k => $v) {
			$args[$k] = implode(',', $v);
		}
		
		foreach ($this->_pipeOptions as $k => $v) {
			$args[$k] = implode('|', $v);
		}
		
		foreach ($this->_commaPipeOptions as $key => $sets) {
			$arr = array();
			foreach ($sets as $set) {
				$arr[] = implode(',', $set);
			}
			if (!empty($arr)) {
				$args[$key] = implode('|', $arr);
			}
		}
		
		$axes = array();
		$axes_labels = array();
		foreach ($this->_axes as $axis => $label_set) {
			foreach ($label_set as $labels) {
				$axes[] = $axis;
				if (!empty($labels)) {
					$axes_labels[] = $labels;
				}
			}
		}
		if (!empty($axes)) {
			$args['chxt'] = implode($axes, ',');
			if (!empty($axes_labels)) {
				$chxl = array();
				foreach ($axes_labels as $labels) {
					$clean_labels = array();
					foreach ($labels as $label) {
						$clean_labels[] = urlencode($label);
					}
					$chxl[] = implode($clean_labels, '|'); 
				}
				for ($i = 0; $i < count($chxl); $i++) {
					$chxl[$i] = $i . ':|' . $chxl[$i];
				}
				$args['chxl'] = implode($chxl, '|');
			}
		}
		
		$query = array();
		foreach ($args as $k => $v) {
			$query[] = $k . '=' . $v;
		}
		$output .= implode($query, '&amp;');
		
		return $output;
	}
	
	public function outputImg($sizeX, $sizeY, $alt='', $params=array()) {
		$output = '<img src="' .
			$this->outputSrc($sizeX, $sizeY) .
			'" alt="' . $alt . '"';
		if (!empty($params)) {
			foreach ($params as $k=>$v) {
				$output .= ' ' . $k . '="' . $v . '"';
			}
		}
		$output .=' />';
			
		return $output;
	}
	
	public function hasData() {
		$data = $this->_data;
		foreach ($data as $index => $set) {
			foreach ($set as $k => $v) {
				if (!empty($v)) {
					return true; // 0, '', false, are all empty  
				}
			}
		}
		return false;
	}
	
}
