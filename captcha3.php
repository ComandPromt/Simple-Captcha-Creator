<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
class SimpleCaptcha {
    public $fontSize = 32; // Tamaño de letra grande
    public $width = 300;   // Ancho ajustado para letras grandes
    public $height = 100;  // Altura aumentada para evitar cortes
    public $fonts = [
        'Candice.ttf', 
        'Ding-DongDaddyO.ttf', 
        'Duality.ttf', 
        'StayPuft.ttf', ];
    public $colors = [
        [27, 78, 181], [22, 163, 35], [214, 36, 7], 
        [244, 148, 41], [218, 43, 227], [235, 59, 196]
    ];
    
    private $session_var;
    private $numChars;
    private $centerText;

    public function __construct($numChars, $session_var = 'captcha') {
        $this->numChars = $numChars;
        $this->session_var = $session_var;
        $this->centerText = true; // Por defecto centrado
    }

    public function CreateImage() {
        $text = $this->GetRandomCaptchaText($this->numChars);
        $_SESSION[$this->session_var] = $text;

        $im = imagecreatetruecolor($this->width, $this->height);
        $bgColor = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, $this->width, $this->height, $bgColor);

        $margin = 30;           // Margen de seguridad para no tocar bordes
        $paddingBetween = 25;   // Espacio generoso entre caracteres
        $amplitude = 8;
        $frequency = 0.05;
        
        // Cálculo para centrar el bloque de texto
        $totalTextWidth = ($this->numChars * $this->fontSize) + (($this->numChars - 1) * $paddingBetween);
        $x = max($margin, ($this->width - $totalTextWidth) / 2);

        for ($i = 0; $i < strlen($text); $i++) {
            $randomColor = $this->colors[array_rand($this->colors)];
            $textColor = imagecolorallocate($im, ...$randomColor);
            
            // Ruta a las fuentes (asegúrate de que exista)
            $font = __DIR__ . '/resources/fonts/' . $this->fonts[array_rand($this->fonts)];
            
            // Cálculo de Y para que esté centrado verticalmente con onda suave
            $y = ($this->height / 2) + ($this->fontSize / 3) + (sin($x * $frequency) * $amplitude);
            
            imagettftext($im, $this->fontSize, rand(-10, 10), $x, $y, $textColor, $font, $text[$i]);
            
            $x += $this->fontSize + $paddingBetween;
        }

        header('Content-Type: image/webp');
        imagewebp($im);
        imagedestroy($im);
    }

    private function GetRandomCaptchaText($length) {
        $chars = 'ABCEFGHJKMNPQRTWXYZ';
        $text = '';
        for ($i = 0; $i < $length; $i++) {
            $text .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $text;
    }
}

$var = isset($_GET['var']) ? $_GET['var'] : 'captcha';

$captcha = new SimpleCaptcha(5, $var);

$captcha->CreateImage();

?>