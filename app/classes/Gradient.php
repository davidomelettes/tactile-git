<?php

class Gradient {
    
	public $width;
	public $height;
	public $startcolour;
	public $endcolour;
	public $step;
	
	public $image;
	
    // Constructor. Creates, fills and returns an image
    function __construct($width, $height, $start_col, $end_col, $step=0) {
    	if (file_exists($this->image_path($start_col, $end_col))) {
    		$this->image = imagecreatefrompng($this->image_path($start_col, $end_col));
    	} else {
	        $this->width = $width;
	        $this->height = $height;
	        $this->startcolour = $start_col;
	        $this->endcolour = $end_col;
	        $this->step = intval(abs($step));
	
	        $this->image = imagecreatetruecolor($this->width, $this->height);
	        
	        // Fill it
	        $this->fill();
	        
	        // Save it
	        $this->write();
    	}
        
        // Show it        
        $this->display();
        
        // Return it
        return $this->image;
    }
    
    public function image_path($start_col, $end_col) {
    	return FILE_ROOT . 'public/graphics/shared/headers/' . str_replace('#', '', strtolower($start_col)) .
    		'-' . str_replace('#', '', strtolower($end_col)).'.png';
    }
    
    public function write() {
    	$file = $this->image_path($this->startcolour, $this->endcolour);
        imagepng($this->image, $file);
    }
    
    public function display () {
        header("Content-type: image/png");
        imagepng($this->image);
        return true;
    }
    
    // The main function that draws the gradient
    public function fill() {
        $line_numbers = imagesy($this->image);
        $line_width = imagesx($this->image);
        list($r1,$g1,$b1) = $this->hex2rgb($this->startcolour);
        list($r2,$g2,$b2) = $this->hex2rgb($this->endcolour);
       
		$r = 0;
		$g = 0;
		$b = 0;
 
        for ( $i = 0; $i < $line_numbers; $i=$i+1+$this->step ) {
            // old values :
            $old_r=$r;
            $old_g=$g;
            $old_b=$b;
            // new values :
            $r = ( $r2 - $r1 != 0 ) ? intval( $r1 + ( $r2 - $r1 ) * ( $i / $line_numbers ) ): $r1;
            $g = ( $g2 - $g1 != 0 ) ? intval( $g1 + ( $g2 - $g1 ) * ( $i / $line_numbers ) ): $g1;
            $b = ( $b2 - $b1 != 0 ) ? intval( $b1 + ( $b2 - $b1 ) * ( $i / $line_numbers ) ): $b1;
            if ( "$old_r,$old_g,$old_b" != "$r,$g,$b") {
                $fill = imagecolorallocate( $this->image, $r, $g, $b );
	    }

            imagefilledrectangle($this->image, 0, $i, $line_width, $i+$this->step, $fill);
        }
    }
    
    public function hex2rgb($colour) {
        $colour = str_replace('#','',$colour);
        $s = strlen($colour) / 3;
        $rgb[]=hexdec(str_repeat(substr($colour,0,$s),2/$s));
        $rgb[]=hexdec(str_repeat(substr($colour,$s,$s),2/$s));
        $rgb[]=hexdec(str_repeat(substr($colour,2*$s,$s),2/$s));
        return $rgb;
    }
}

