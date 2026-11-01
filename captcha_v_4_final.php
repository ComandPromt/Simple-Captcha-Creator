<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

class SimpleCaptcha {
    public $fontSize = 50; 
    public $width = 340;   
    public $height = 100;  
    public $fonts = ['1.ttf', '2.woff', '3.woff', '4.woff', '5.ttf', '6.woff'];
    public $colors = [[27, 78, 181], [22, 163, 35], [214, 36, 7], [244, 148, 41]];    
    private $session_var;
    private $numChars;
    private $centerText;

    public function __construct($numChars, $session_var = 'captcha') {
        $this->numChars = $numChars;
        $this->session_var = $session_var;
        $this->centerText = true;
    }

    public function CreateImage() {
        $text = $this->GetRandomCaptchaText($this->numChars);
        $_SESSION[$this->session_var] = $text;

        $im = imagecreatetruecolor($this->width, $this->height);
        $bgColor = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, $this->width, $this->height, $bgColor);

        $amplitude = 6;         
        $frequency = 0.05;
        $minMargin = 25;        // Margen de seguridad lateral absoluto
        $minDistance = 22;      // Distancia mínima estricta entre los límites reales de las letras
        $topMarginLimit = 18;   // Margen superior estricto

        // 1. Preparamos fuentes, colores y ángulos barajados sin repetición
        $availableFonts = $this->fonts;
        shuffle($availableFonts);

        $availableColors = $this->colors;
        shuffle($availableColors);

        $availableAngles = [-13, -11, -9, -7, 7, 9, 11, 13];
        shuffle($availableAngles);

        // 2. Pre-calculamos las posiciones exactas usando el bounding box real
        $charsData = [];
        $currentX = 0;

        for ($i = 0; $i < strlen($text); $i++) {
            $fontFile = __DIR__ . '/resources/fonts/' . $availableFonts[$i % count($availableFonts)];
            $angle = $availableAngles[$i % count($availableAngles)];
            
            // Obtenemos la caja delimitadora real de la letra rotada
            $bbox = imagettfbbox($this->fontSize, $angle, $fontFile, $text[$i]);
            
            $minX = min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $maxX = max($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $minY = min($bbox[1], $bbox[3], $bbox[5], $bbox[7]); 
            
            if ($i > 0) {
                $prevMaxX = $charsData[$i - 1]['maxX'];
                $currentX = $charsData[$i - 1]['relX'] + $prevMaxX - $minX + $minDistance;
            } else {
                $currentX = 0;
            }

            $charsData[$i] = [
                'char'  => $text[$i],
                'minX'  => $minX,
                'maxX'  => $maxX,
                'minY'  => $minY,
                'angle' => $angle,
                'font'  => $fontFile,
                'color' => $availableColors[$i % count($availableColors)],
                'relX'  => $currentX
            ];
        }

        // 3. Calculamos los límites globales del bloque de texto para centrarlo perfectamente
        $firstChar = $charsData[0];
        $lastChar = $charsData[count($charsData) - 1];
        
        $textMinX = $firstChar['relX'] + $firstChar['minX'];
        $textMaxX = $lastChar['relX'] + $lastChar['maxX'];

        $offsetX = max($minMargin - $textMinX, ($this->width - ($textMinX + $textMaxX)) / 2);

        // 4. Dibujado final con validación estricta del margen superior
        foreach ($charsData as $data) {
            $x = $offsetX + $data['relX'];
            
            $textColor = imagecolorallocate($im, ...$data['color']);
            
            $y = ($this->height / 2) + ($this->fontSize / 3) + (sin($x * $frequency) * $amplitude);
            
            $highestPoint = $y + $data['minY'];
            if ($highestPoint < $topMarginLimit) {
                $y += ($topMarginLimit - $highestPoint);
            }
            
            imagettftext($im, $this->fontSize, $data['angle'], $x, $y, $textColor, $data['font'], $data['char']);
        }

        header('Content-Type: image/webp');
        imagewebp($im);
        imagedestroy($im);
    }

    private function GetRandomCaptchaText($length) {
        $chars = 'ABCEFGHJKMNPQRTWXYZ';
        // Barajamos el set completo de caracteres y recortamos los primeros '$length' para evitar letras repetidas
        $shuffled = str_shuffle($chars);
        return substr($shuffled, 0, $length);
    }
}

$var = isset($_GET['var']) ? $_GET['var'] : 'captcha';
$captcha = new SimpleCaptcha(4, $var);
$captcha->CreateImage();
?>
