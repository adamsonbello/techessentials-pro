<?php
if (extension_loaded('gd')) {
    echo "✅ Extension GD activée !<br>";
    echo "Fonctions disponibles :<br>";
    echo "- imagecreatefromjpeg: " . (function_exists('imagecreatefromjpeg') ? '✅' : '❌') . "<br>";
    echo "- imagecreatefrompng: " . (function_exists('imagecreatefrompng') ? '✅' : '❌') . "<br>";
    echo "- imagewebp: " . (function_exists('imagewebp') ? '✅' : '❌') . "<br>";
    echo "<br>Infos GD:<br>";
    print_r(gd_info());
} else {
    echo "❌ Extension GD NON activée";
}
?>