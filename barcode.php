<?php
require_once __DIR__ . '/vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

// Configuración
$defaultCode = '0112345678901231';
$code = $_GET['code'] ?? $defaultCode;
$action = $_GET['action'] ?? 'show';

// Validación
if (!preg_match('/^\d+$/', $code)) {
    die("Error: El código debe contener solo números");
}

$formattedCode = '(01)' . $code;
$generator = new BarcodeGeneratorPNG();
$barcodeImage = $generator->getBarcode($formattedCode, $generator::TYPE_CODE_128, 2, 50);

// Crear imagen combinada con el texto
$im = imagecreatefromstring($barcodeImage);
$width = imagesx($im);
$height = imagesy($im);

// Añadir espacio para el texto
$textHeight = 30; // Altura adicional para el texto
$newHeight = $height + $textHeight;
$newIm = imagecreatetruecolor($width, $newHeight);

// Fondo blanco
$white = imagecolorallocate($newIm, 255, 255, 255);
imagefill($newIm, 0, 0, $white);

// Copiar el código de barras
imagecopy($newIm, $im, 0, 0, 0, 0, $width, $height);

// Añadir texto
$black = imagecolorallocate($newIm, 0, 0, 0);
$font = 5; // Fuente interna de GD (puedes usar una fuente TrueType con imagettftext)
$textWidth = imagefontwidth($font) * strlen($formattedCode);
$x = ($width - $textWidth) / 2;
imagestring($newIm, $font, $x, $height + 5, $formattedCode, $black);

if ($action === 'download') {
    // Descargar la imagen
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="codigo_barras_'.$code.'.png"');
    imagepng($newIm);
    imagedestroy($newIm);
    exit;
} else {
    // Mostrar en HTML
    ob_start();
    imagepng($newIm);
    $imageData = ob_get_clean();
    imagedestroy($newIm);
    
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            .barcode-container {
                text-align: center;
                margin: 50px auto;
                max-width: 500px;
                padding: 20px;
            }
            .barcode-img {
                max-width: 100%;
                height: auto;
                margin-bottom: 15px;
                border: 1px solid #eee;
            }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background: #27ae60;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                margin: 10px 5px;
            }
        </style>
    </head>
    <body>
        <div class="barcode-container">
            <img class="barcode-img" src="data:image/png;base64,<?= base64_encode($imageData) ?>" alt="Código de barras">
            <div>
                <a href="barcode.php?code=<?= $code ?>&action=download" class="btn">Descargar Imagen</a>
                <a href="index.html" class="btn" style="background: #3498db;">Generar Otro</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}