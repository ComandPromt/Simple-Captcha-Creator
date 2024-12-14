<?php

session_start();

$captcha = new SimpleCaptcha();

$captcha->lineWidth = 3;

$captcha->scale = 3; $captcha->blur = true;

// Image generation

$captcha->CreateImage();

class SimpleCaptcha {

    /** 
     * Difficulty level (if used, keep 'Wave configuracion' on default)
     * normal = 1 
     * closer to 0 is more easy, for ex: 0.4
     * closer to 2 is more hard, for ex: 1.8
     * smaller then 0 and bigger than 2 is caped to min or max
     */

   public $fontSize = 50;  // Tamaño de la fuente
    public $xPosition = 3;  // Posición X del texto
    public $yPosition = 90; // Posición Y del texto
    public $lineWidth = 3;  // Ancho de la línea
    public $scale = 3;   
	private $angle;
    public $difficulty = 1;

    /** Width of the image */

    public $width  = 100;

    /** Height of the image */

    public $height = 70;
  
    public $resourcesPath = 'resources';

    /** Min word length (for non-dictionary random text generation) */
	
    public $minWordLength =4;

    /**
     * Max word length (for non-dictionary random text generation)
     * 
     * Used for dictionary words indicating the word-length
     * for font-size modification purposes
     */
	 
    public $maxWordLength = 5;

    /** Sessionname to store the original text */
	
    public $session_var = 'captcha';

    /** Background color in RGB-array */
	
    public $backgroundColor = array(255, 255, 255);

    /** Foreground colors in RGB-array */
	
    public $colors = array(
        array(27,78,181), // blue
        array(22,163,35), // green
        array(214,36,7),  // red
    );

    /** Shadow color in RGB-array or null */

    public $shadowColor = null; //array(0, 0, 0);

    /**
     * Font configuration
     *
     * - font: TTF file
     * - spacing: relative pixel space between character
     * - minSize: min font size
     * - maxSize: max font size
     */

    public $fonts = array(
        'Candice'  => array('font' => 'Candice.ttf'),
        'DingDong' => array('font' => 'Ding-DongDaddyO.ttf'),
        'Duality'  => array('font' => 'Duality.ttf'),
        'StayPuft' => array('font' => 'StayPuft.ttf')

    );

    /** Wave configuracion in X and Y axes */

    public $Yperiod    = 10;

    public $Yamplitude = 40;

    public $Xperiod    = 10;

    public $Xamplitude = 10;

    /** letter rotation clockwise */

    public $maxRotation = 4;

    /** GD image */

    public $im;

    public function __construct($config = array()) {
		
		if (isset($config['fontSize'])) {
            $this->fontSize = $config['fontSize'];
        }
		
        if (isset($config['xPosition'])) {
            $this->xPosition = $config['xPosition'];
        }
        
		if (isset($config['yPosition'])) {
            $this->yPosition = $config['yPosition'];
        }
        
		if (isset($config['lineWidth'])) {
            $this->lineWidth = $config['lineWidth'];
        }
        
		if (isset($config['scale'])) {
            $this->scale = $config['scale'];
        }
		
		if (isset($config['angle'])) {
            $this->angle = $angle;
        }
		
	}

    public function CreateImage() {

        $ini = microtime(true);

        // cap difficulty

        if($this->difficulty >2) $this->difficulty = 2;

        if($this->difficulty<=0) $this->difficulty = 0.1;

        /** Initialization */

        $this->ImageAllocate();
        
        /** Text insertion */

        $fontcfg  = $this->fonts[array_rand($this->fonts)];

        $this->WriteText($text, $fontcfg);

          /** Transformations */

        if (!empty($this->lineWidth)) {

            $this->WriteLine();

        }

        if ($this->blur && function_exists('imagefilter')) {

            imagefilter($this->im, IMG_FILTER_GAUSSIAN_BLUR);

        }

        $this->ReduceImage();

        if ($this->debug) {

            imagestring($this->im, 1, 1, $this->height-8,
                "$text {$fontcfg['font']} ".round((microtime(true)-$ini)*1000)."ms",
                $this->GdFgColor
            );

        }

        /** Output */

        $this->WriteImage($fontcfg['font']);

        $this->Cleanup();

    }

    /**
     * Creates the image resources
     */

    protected function ImageAllocate() {

        // Cleanup

        if (!empty($this->im)) {

            imagedestroy($this->im);
        
		}

        $this->im = imagecreatetruecolor($this->width*$this->scale, $this->height*$this->scale);

        // Background color
        
		$this->GdBgColor = imagecolorallocate($this->im,
            $this->backgroundColor[0],
            $this->backgroundColor[1],
            $this->backgroundColor[2]
        );
        
		imagefilledrectangle($this->im, 0, 0, $this->width*$this->scale, $this->height*$this->scale, $this->GdBgColor);

        // Foreground color
        
		$color           = $this->colors[mt_rand(0, sizeof($this->colors)-1)];
        
		$this->GdFgColor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);

        // Shadow color
        
		if (!empty($this->shadowColor) && is_array($this->shadowColor) && sizeof($this->shadowColor) >= 3) {
        
			$this->GdShadowColor = imagecolorallocate($this->im,
                $this->shadowColor[0],
                $this->shadowColor[1],
                $this->shadowColor[2]
            );
    
		}
    
	}

    /**
     * Random text generation
     *
     * @return string Text
     */
	 
    protected function GetRandomCaptchaText($length = null) {
     
		if (empty($length)) {

            $length = rand($this->minWordLength, $this->maxWordLength);

        }

        $words  = "?!0123456789abcdefghijlmnopqrstvwyz";
        
		$vocals = "aeiou";

        $text  = "";
        
		$vocal = rand(0, 1);
        
		for ($i=0; $i<$length; $i++) {
        
			if ($vocal) {

                $text .= substr($vocals, mt_rand(0, 4), 1);

            }

			else {

                $text .= substr($words, mt_rand(0, 22), 1);
            }

            $vocal = !$vocal;

        }

        return $text;

    }

    /**
     * Horizontal line insertion
     */

    protected function WriteLine() {

        $x1 = $this->width*$this->scale*.15;

        $x2 = $this->textFinalX;

        $y1 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);

        $y2 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);

        $width = $this->lineWidth/2*$this->scale;

        for ($i = $width*-1; $i <= $width; $i++) {

            imageline($this->im, $x1, $y1+$i, $x2, $y2+$i, $this->GdFgColor);

        }

    }

    /**
     * Text insertion
     */

    protected function WriteText($text, $fontcfg = array()) {

        if (empty($fontcfg)) {

            // Select the font configuration

            $fontcfg  = $this->fonts[array_rand($this->fonts)];

        }

        // Full path of font file

        $fontfile = $this->resourcesPath.'/fonts/'.$fontcfg['font'];

        /** Increase font-size for shortest words: 9% for each glyp missing */

        $lettersMissing = $this->maxWordLength-strlen($text);

        $fontSizefactor = 1+($lettersMissing*0.09);

        // Text generation (char by char)

        $x      = 20*$this->scale;

        $y      = round(($this->height*27/40)*$this->scale);

        $length = strlen($text);

        for ($i=0; $i<$length; $i++) {

            $degree   = rand($this->maxRotation*-1, $this->maxRotation)*$this->difficulty;

            $fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize'])*$this->scale*$fontSizefactor;

            $letter   = substr($text, $i, 1);

            if ($this->shadowColor) {

                $coords = imagettftext($this->im, $fontsize, $degree,
                    $x+$this->scale, $y+$this->scale,
                    $this->GdShadowColor, $fontfile, $letter);

            }

            $coords = imagettftext($this->im, $fontsize, $degree,
                $x, $y,
                $this->GdFgColor, $fontfile, $letter);
            $x += ($coords[2]-$x) + ($fontcfg['spacing']*$this->scale);

        }

        $this->textFinalX = $x;
    }

    /**
     * Wave filter
     */

    protected function WaveImage() {

        // create wave difficulty

        $wdf = 1;

        if($this->difficulty<1) $wdf = 1/$this->difficulty*(0.9/$this->difficulty);

        if($this->difficulty>1) $wdf = (1/($this->difficulty*1.7))+0.5;

        // X-axis wave generation

        $xp = $this->scale*$this->Xperiod*rand(1,3) * $wdf;

        $k = rand(1, 100);

        for ($i = 0; $i < ($this->width*$this->scale); $i++) {

            imagecopy($this->im, $this->im,
                $i-1, sin($k+$i/$xp) * ($this->scale*$this->Xamplitude),
                $i, 0, 1, $this->height*$this->scale);

        }

        // Y-axis wave generation

        $k = rand(0, 100);

        $yp = $this->scale*($this->Yperiod)*rand(1,2) * $wdf; 

        for ($i = 0; $i < ($this->height*$this->scale); $i++) {

            imagecopy($this->im, $this->im,
                sin($k+$i/$yp) * ($this->scale*$this->Yamplitude), $i-1,
                0, $i, $this->width*$this->scale, 1);

        }

    }

    /**
     * Reduce the image to the final size
     */

    protected function ReduceImage() {
       
	   // Reduzco el tamaño de la imagen

        $imResampled = imagecreatetruecolor($this->width, $this->height);

        imagecopyresampled($imResampled, $this->im,
            0, 0, 0, 0,
            $this->width, $this->height,
            $this->width*$this->scale, $this->height*$this->scale
        );

        imagedestroy($this->im);

        $this->im = $imResampled;

    }

    protected function WriteImage($font) {
		
		$fondo=mt_rand(1,9);
		
		$color=mt_rand(1,7);
		
		switch($color){
		
			case 1:
			
			//blanco
			
			$red=239;
			
			$green=239;
			
			$blue=239;
			
			break;
			
			case 2:
			
			// verde
			
			$red=22;
			
			$green=163;
			
			$blue=35;
			
			break;
			
			case 3:
			
			//azul
			
			$red=27;
			
			$green=78;
			
			$blue=181;
			
			break;
			
			case 4:
			
			// morado
			
			$red=130;
			
			$green=37;
			
			$blue=163;
			
			break;
			
			case 5:
			
			// amarillo
			
			$red=216;
			
			$green=216;
			
			$blue=17;
			
			break;
			
			case 6:
			
			//naranja
			
			$red=243;
			
			$green=109;
			
			$blue=36;
			
			break;
			
			case 7:
			
			// rojo
			
			$red=242;
			
			$green=10;
			
			$blue=60;
			
			break;
		
		}
		
		if ($red == 239 && ($fondo == 1 || $fondo == 2|| $fondo == 3 || $fondo == 4|| $fondo == 5)) {
		
			$fondo = 6;

		}

		else if ($red == 243 && ($fondo == 1 || $fondo=8)) {
			
			$fondo = 7;
		
		}

		else if ($red == 130 && ($fondo == 7 || $fondo == 9 || $fondo == 8 || $fondo == 4 || $fondo == 3 || $fondo == 6)) {

			$fondo = 5;

		} 
		
		else if (
			($red == 216 && ($fondo == 1 || $fondo == 2 || $fondo == 3 || $fondo == 4 || $fondo == 5)) || 
			($red == 243 && $fondo == 4) || 
			($red == 22 && ($fondo == 1 || $fondo == 2|| $fondo == 4)) || 
			($red == 27 && ($fondo == 1|| $fondo == 2|| $fondo == 5 || $fondo == 7|| $fondo == 8)) || 
			($red == 242 && ($fondo == 4 ||$fondo == 6 || $fondo == 1 || $fondo == 8))
		){

			$fondo = 9;

		}
		
		$this->im = @imagecreatefrompng('resources/fondos/'.$fondo.'.png');
		
		$text = $this->GetRandomCaptchaText();
		
		$_SESSION[$this->session_var] = $text;
		
		$this->WaveImage();
		
		imagettftext($this->im, $this->fontSize, $this->angle, $this->xPosition, $this->yPosition, imagecolorallocate($this->im, $red, $green, $blue), 'resources/fonts/'.$font, $text);
  
		header("Content-type: image/png");
		
		imagepng($this->im);
	
    }

    protected function Cleanup() {
        
		imagedestroy($this->im);
    
	}

}

?>
