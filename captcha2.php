<?php

session_start();

class SimpleCaptcha {
    
	public $fontSize = 20;
    
	public $width = 200; // Ancho de la imagen
    
	public $height = 60; // Altura de la imagen
    
	public $fonts = [
    'Candice.ttf', 
    'Ding-DongDaddyO.ttf', 
    'Duality.ttf', 
    'StayPuft.ttf', ]; // Ruta a las fuentes en tu servidor
    
	public $backgroundColor = [255, 255, 255]; // Color de fondo (opcional si no se usa una imagen)
    
	public $colors = [
        [27, 78, 181], // azul
        [22, 163, 35], // verde
        [214, 36, 7],  // rojo
		[244,148,41], // naranja
		[218,43,227], // morado
		[0,0,0],      // negro
		[235,59,196], // rosa	
		[233,235,59], //amarillo	
		[235,59,124], // rojizo
		[59,235,186], // verde agua
		[59,235,141], // verde hoja
		[131,235,59], // verde botella
		[199,235,59], // verde oliva
		[235,223,59], // dorado
		[218,218,218], // gris
		[255, 255, 255], //blanco
    ];
	
    public $session_var;
    
	private $numChars; // Número de caracteres en el CAPTCHA
    
	private $backgroundImages = ['1.png', '2.png', '3.png', '4.png', '5.png', '6.png', '7.png', '8.png', '9.png']; // Lista de imágenes de fondo disponibles en el servidor
    
	private $centerText; // Booleano para centrar el texto
    
	private $xOffset = 0; // Desplazamiento horizontal en píxeles
    
	private $useContrastEffect; // Variable para controlar si se debe usar el efecto de contraste

    /**
     * Constructor que inicializa el número de caracteres del CAPTCHA, el ancho de la imagen, si centrar el texto, el desplazamiento horizontal y el efecto de contraste.
     *
     * @param int $numChars Número de caracteres del CAPTCHA.
     * @param int $width Ancho de la imagen.
     * @param bool $centerText Si es true, centra el texto.
     * @param int $xOffset Desplazamiento en píxeles en la dirección X.
     * @param bool $useContrastEffect Si es true, utiliza el efecto de contraste de colores.
     */
	 
   public function __construct($numChars, $width = 200, $centerText = false, $xOffset = 0, $useContrastEffect = false, $session_var = 'captcha') {
        
		$this->numChars = $numChars;
        
		$this->width = $width;
        
		$this->centerText = $centerText;
        
		$this->xOffset = $xOffset;
        
		$this->useContrastEffect = $useContrastEffect;
        
		$this->session_var = $session_var;
    
	}

    /**
     * Crea y muestra la imagen del CAPTCHA.
     */
	 
    public function CreateImage() {

        $text = $this->GetRandomCaptchaText($this->numChars);
        
		$_SESSION[$this->session_var] = $text;

        $im = imagecreatetruecolor($this->width, $this->height);

        $background = $this->backgroundImages[array_rand($this->backgroundImages)];
        
		$bgPath = __DIR__ . '/resources/fondos/' . $background;

        $bgImage = null;

        if (file_exists($bgPath)) {
			
            $bgImage = imagecreatefrompng($bgPath); // Cargar la imagen de fondo
            
			imagecopyresized($im, $bgImage, 0, 0, 0, 0, $this->width, $this->height, imagesx($bgImage), imagesy($bgImage));
            
			imagedestroy($bgImage);
			
        } 
		
		else {
			
            $bgColor = imagecolorallocate($im, ...$this->backgroundColor);
            
			imagefilledrectangle($im, 0, 0, $this->width, $this->height, $bgColor);
        
		}

        $phaseShift = 180; // Cambia el desplazamiento de fase aleatoriamente
        
		$amplitude = 10; // Define la amplitud máxima de la onda
        
		$frequency = 20; // Controla la frecuencia de la onda
        
		$x = 10; // Posición inicial en X
        
		$angle = rand(-10, 10); // Define un ángulo inicial aleatorio

        // Si se debe centrar el texto, calcular la posición inicial X
        
		if ($this->centerText) {
            
			// Calcular el ancho total del texto
            
			$textWidth = 0;
            
			for ($i = 0; $i < strlen($text); $i++) {
                
				$textWidth += $this->fontSize + rand(5, 8);
           
			}
			
            // Centrar el texto
			
            $x = ($this->width - $textWidth) / 2;
			
        }

		else {
			
            // Si no se centra, aplicar el desplazamiento en X
			
            $x += $this->xOffset;
			
        }

        // Verificar si se está usando el efecto de contraste
		
        if ($this->useContrastEffect) {
			
            // Si el efecto de contraste está habilitado, aplicar el contraste basado en la imagen de fondo
            
			$textColor = $this->GetContrastingColor($bgImage, $x, $amplitude);
        
		}
		
		else {
			
            // Si no se usa el efecto de contraste, usar colores aleatorios por cada letra
            
			for ($i = 0; $i < strlen($text); $i++) {
				
                // Si la imagen de fondo es 1.png, forzar color azul para todo el texto
                
				switch($background){
					
                    case '1.png':
					
                    case '6.png':
					
                        $this->eliminarColor([214, 36, 7]); // Eliminar color rojo
						
                        if($background == '6.png'){
							
                            $this->eliminarColor([27, 78, 181]); // Eliminar color azul
							$this->eliminarColor([0, 0, 0]);
                        }
						
						else{
							
							// case 1
							$this->eliminarColor([255,255,255]);
							
							$this->eliminarColor([235,223,59]);
							
							$this->eliminarColor([235,59,196]);
							
							$this->eliminarColor([59,235,186]);
							
							$this->eliminarColor([59,235,141]);
							
							$this->eliminarColor([131,235,59]);
							
							$this->eliminarColor([244,148,41]);
														
							$this->eliminarColor([235,59,124]);
							
							$this->eliminarColor([199,235,59]);
							
							$this->eliminarColor([218,43,227]);
							
							$this->eliminarColor([233,235,59]);
							
							$this->eliminarColor([218, 218, 218]);
							
							$this->eliminarColor([22, 163, 35]);
							
						}
						
                        break;
						
                    case '8.png':
					
						$this->eliminarColor([235,223,59]);
					
						$this->eliminarColor([218,43,227]);
					
						$this->eliminarColor([233,235,59]);
					
						$this->eliminarColor([244,148,41]);
					
						$this->eliminarColor([0, 0, 0]);
					
                        $this->eliminarColor([27, 78, 181]); // Eliminar color azul
						
                        $this->eliminarColor([214, 36, 7]); // Eliminar color rojo
						
                        break;
						
                    case '3.png':
										
						$this->eliminarColor([244,148,41]);
					
						$this->eliminarColor([233,235,59]);
						
						$this->eliminarColor([235,59,124]);
						
						$this->eliminarColor([235,59,196]); 
                        
						$this->eliminarColor([255, 255, 255]); // Eliminar color blanco
						
                        $this->eliminarColor([22, 163, 35]); // Eliminar color verde
						
						$this->eliminarColor([218,43,227]);
						
						$this->eliminarColor([218,218,218]);
						
						$this->eliminarColor([235,223,59]);
						
						$this->eliminarColor([199,235,59]);
						
						$this->eliminarColor([131,235,59]);
						
						$this->eliminarColor([59,235,141]);
						
						$this->eliminarColor([59,235,186]);
												
                        break;
						
					   case '4.png':
				
						$this->eliminarColor([214, 36, 7]);
				
						$this->eliminarColor([255,255,255]);
				
						$this->eliminarColor([59,235,141]);
						
						$this->eliminarColor([218,43,227]);
						
						$this->eliminarColor([235,59,196]);
											
						$this->eliminarColor([59,235,186]);
						
						$this->eliminarColor([244,148,41]);
						
						$this->eliminarColor([235,223,59]);
                        
						$this->eliminarColor([235,59,124]);
						
						$this->eliminarColor([131,235,59]);
                        
						$this->eliminarColor([199,235,59]);
						
						$this->eliminarColor([233,235,59]);
						
						$this->eliminarColor([218,218,218]);
                        
						break;	
						
                    case '5.png':
					
                    case '7.png':
					
                        $this->eliminarColor([27, 78, 181]); // Eliminar color azul
						
                        if($background == '5.png'){
							
							$this->eliminarColor([218,43,227]);
							
							$this->eliminarColor([235,59,196]);
							
							$this->eliminarColor([244,148,41]);
														
							$this->eliminarColor([199,235,59]);
							
							$this->eliminarColor([235,223,59]);
												
							$this->eliminarColor([233,235,59]);
							
							$this->eliminarColor([59,235,141]);
                            
							$this->eliminarColor([255, 255, 255]); // Eliminar color blanco
							
							$this->eliminarColor([22, 163, 35]);
							 
							$this->eliminarColor([59,235,186]);
							 
							$this->eliminarColor([199,235,59]);
							
							$this->eliminarColor([131,235,59]);
							
							$this->eliminarColor([218,218,218]);
							
                        }
						
						else{
						
							$this->eliminarColor([0, 0, 0]);
						
						}
						
                        break;
						
						case '2.png':
	 
							$this->eliminarColor([235,59,196]);
		
							$this->eliminarColor([218,43,227]);
							
							$this->eliminarColor([244,148,41]);
		
							$this->eliminarColor([233,235,59]);
							
							$this->eliminarColor([59,235,186]);
							
							$this->eliminarColor([59,235,141]);
							
							$this->eliminarColor([131,235,59]);
							
							$this->eliminarColor([199,235,59]);
							
							$this->eliminarColor([235,223,59]);
									
							$this->eliminarColor([22, 163, 35]);
							
							$this->eliminarColor([218, 218, 218]);
														
						break;
						
						case '9.png':
						
							$this->eliminarColor([0, 0, 0]);
						 
							$this->eliminarColor([218, 218, 218]);
						 
						break;
                   
                }
                
                $randomColor = $this->colors[array_rand($this->colors)];
				
                $textColor = imagecolorallocate($im, ...$randomColor);

                // Escribir la letra con el color correspondiente
				
                $letter = $text[$i];

                // Calcula la posición Y con la onda sinusoidal
				
                $y = (($this->height / 2) + sin(deg2rad($x * $frequency + $phaseShift)) * $amplitude) + 8;

                // Dibuja la letra con el efecto wave
				
				$font = __DIR__ . '/resources/fonts/' . $this->fonts[array_rand($this->fonts)];
				
                imagettftext($im, $this->fontSize, $angle, $x, $y, $textColor, $font, $letter);

                // Incrementa X y varía ligeramente el ángulo para dar un efecto más dinámico
				
                $x += $this->fontSize + rand(5, 8);
				
                $angle += rand(-5, 5); // Variación del ángulo entre letras
				
            }
			
        }

        header('Content-Type: image/png');
		
        imagepng($im);
		
        imagedestroy($im);
		
    }

    /**
     * Elimina un color de la lista de colores.
     *
     * @param array $color Color que se debe eliminar (por ejemplo, [214, 36, 7]).
     */
	 
    private function eliminarColor($color) {
        
		$this->colors = array_filter($this->colors, function($c) use ($color) {
            
			return $c !== $color;
			
        });
		
        $this->colors = array_values($this->colors); // Reindexar el array
    }

    /**
     * Genera un texto aleatorio para el CAPTCHA.
     *
     * @param int $length Longitud del texto.
     * @return string Texto aleatorio generado.
     */
	 
    private function GetRandomCaptchaText($length) {
        
		$chars = '01@?ABCDEFGHIJKLMNOPQRSTUVWXYZ23456789!abcdefghijklmnopqrstuvwxyz'; // Texto más simple para mejor legibilidad
        
		$text = '';
        
		for ($i = 0; $i < $length; $i++) {
			
            $text .= $chars[rand(0, strlen($chars) - 1)];
			
        }
		
        return $text;
		
    }

    /**
     * Devuelve el color de texto con el mejor contraste según el fondo.
     *
     * @param resource $bgImage Imagen de fondo.
     * @param int $x Posición X inicial para calcular el color.
     * @param int $amplitude Amplitud de la onda para calcular la posición Y.
     * @return int Color del texto.
     */
	 
    private function GetContrastingColor($bgImage, $x, $amplitude) {
        
		// Obtener el color promedio del fondo en el punto donde se va a colocar el texto
        
		$rgb = imagecolorat($bgImage, $x, $this->height / 2 + $amplitude);
        
		$r = ($rgb >> 16) & 0xFF;
        
		$g = ($rgb >> 8) & 0xFF;
        
		$b = $rgb & 0xFF;

        // Calcular el brillo del color de fondo
        
		$brightness = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b);

        // Si el fondo es oscuro, usar color claro (blanco), si es claro, usar color oscuro (negro)
        
		if ($brightness < 128) {
			
            return imagecolorallocate($bgImage, 255, 255, 255); // Blanco
        
		}

		else {
			
            return imagecolorallocate($bgImage, 0, 0, 0); // Negro
        
		}
    
	}

}

// Crear y mostrar el CAPTCHA con 5 caracteres, sin el efecto de contraste, centrado y desplazamiento de 30 píxeles en X

$captcha = new SimpleCaptcha(5, 150, true, 30, false,'captcha2'); // Aquí se establece 'false' para no usar el contraste

$captcha->CreateImage();

?>
