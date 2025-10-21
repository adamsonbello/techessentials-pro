<?php
/**
 * Système d'optimisation automatique d'images pour TechessentialsPro
 * Compression + WebP + Thumbnails
 * 
 * Fichier : admin-blog/includes/image-optimizer.php
 */

class ImageOptimizer {
    
    private $upload_dir;
    private $optimized_dir;
    private $base_url; // AJOUT
    private $max_width = 1920;
    private $quality_jpeg = 85;
    private $quality_webp = 80;
    
    private $sizes = [
        'thumbnail' => 300,
        'medium' => 800,
        'large' => 1200,
        'full' => 1920
    ];
    
    public function __construct() {
        $this->upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/TechEssentialsPro/uploads/';
        $this->optimized_dir = $this->upload_dir . 'optimized/';
        $this->base_url = '/TechEssentialsPro/uploads/optimized/'; // AJOUT
        
        $this->createDirectories();
    }
    
    private function createDirectories() {
        $dirs = [
            $this->upload_dir,
            $this->optimized_dir,
            $this->optimized_dir . 'thumbnail/',
            $this->optimized_dir . 'medium/',
            $this->optimized_dir . 'large/',
            $this->optimized_dir . 'full/',
            $this->optimized_dir . 'webp/'
        ];
        
        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    public function optimize($file) {
        $validation = $this->validateFile($file);
        if (!$validation['success']) {
            return $validation;
        }
        
        $original_name = pathinfo($file['name'], PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $unique_name = $this->generateUniqueName($original_name);
        
        $source_image = $this->loadImage($file['tmp_name'], $extension);
        if (!$source_image) {
            return ['success' => false, 'error' => 'Impossible de charger l\'image'];
        }
        
        list($orig_width, $orig_height) = getimagesize($file['tmp_name']);
        
        $result = [
            'success' => true,
            'original_name' => $file['name'],
            'unique_name' => $unique_name,
            'original_size' => $file['size'],
            'optimized_sizes' => [],
            'urls' => []
        ];
        
        foreach ($this->sizes as $size_name => $max_size) {
            if ($orig_width <= $max_size && $size_name != 'full') {
                continue;
            }
            
            $dimensions = $this->calculateDimensions($orig_width, $orig_height, $max_size);
            
            $resized = imagecreatetruecolor($dimensions['width'], $dimensions['height']);
            
          // Ne pas agrandir si l'image est plus petite (sauf pour full qui est toujours créé)
            if ($orig_width < $max_size && $size_name != 'full') {
                 continue;
            }
            
            imagecopyresampled(
                $resized, $source_image,
                0, 0, 0, 0,
                $dimensions['width'], $dimensions['height'],
                $orig_width, $orig_height
            );
            
            $filename = "{$unique_name}_{$size_name}";
            
            $jpeg_path = $this->optimized_dir . $size_name . '/' . $filename . '.jpg';
            $this->saveAsJpeg($resized, $jpeg_path);
            $jpeg_size = filesize($jpeg_path);
            
            $webp_path = $this->optimized_dir . 'webp/' . $filename . '.webp';
            $this->saveAsWebP($resized, $webp_path);
            $webp_size = filesize($webp_path);
            
            $result['optimized_sizes'][$size_name] = [
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
                'jpeg_size' => $jpeg_size,
                'webp_size' => $webp_size,
                'compression_ratio' => round((1 - $webp_size / $jpeg_size) * 100, 1)
            ];
            
            // CORRECTION ICI : Utiliser $this->base_url au lieu de /uploads/optimized/
            $result['urls'][$size_name] = [
                'jpeg' => $this->base_url . $size_name . '/' . $filename . '.jpg',
                'webp' => $this->base_url . 'webp/' . $filename . '.webp'
            ];
            
            imagedestroy($resized);
        }
        
        imagedestroy($source_image);
        
        $total_jpeg = array_sum(array_column($result['optimized_sizes'], 'jpeg_size'));
        $total_webp = array_sum(array_column($result['optimized_sizes'], 'webp_size'));
        $result['total_saving'] = $file['size'] - $total_webp;
        $result['total_saving_percent'] = round((1 - $total_webp / $file['size']) * 100, 1);
        
        return $result;
    }
    
    private function validateFile($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Erreur lors de l\'upload'];
        }
        
        if ($file['size'] > 10 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Image trop volumineuse (max 10 MB)'];
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            return ['success' => false, 'error' => 'Type de fichier non autorisé'];
        }
        
        return ['success' => true];
    }
    
    private function loadImage($filepath, $extension) {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($filepath);
            case 'png':
                return imagecreatefrompng($filepath);
            case 'gif':
                return imagecreatefromgif($filepath);
            case 'webp':
                return imagecreatefromwebp($filepath);
            default:
                return false;
        }
    }
    
    private function calculateDimensions($orig_width, $orig_height, $max_size) {
        $ratio = $orig_width / $orig_height;
        
        if ($orig_width > $orig_height) {
            $new_width = min($orig_width, $max_size);
            $new_height = round($new_width / $ratio);
        } else {
            $new_height = min($orig_height, $max_size);
            $new_width = round($new_height * $ratio);
        }
        
        return [
            'width' => $new_width,
            'height' => $new_height
        ];
    }
    
    private function saveAsJpeg($image, $filepath) {
        $width = imagesx($image);
        $height = imagesy($image);
        $jpeg_image = imagecreatetruecolor($width, $height);
        
        $white = imagecolorallocate($jpeg_image, 255, 255, 255);
        imagefill($jpeg_image, 0, 0, $white);
        
        imagecopy($jpeg_image, $image, 0, 0, 0, 0, $width, $height);
        
        imagejpeg($jpeg_image, $filepath, $this->quality_jpeg);
        imagedestroy($jpeg_image);
    }
    
    private function saveAsWebP($image, $filepath) {
        if (function_exists('imagewebp')) {
            imagewebp($image, $filepath, $this->quality_webp);
        } else {
            return false;
        }
    }
    
    private function generateUniqueName($original_name) {
        $slug = $this->slugify($original_name);
        $timestamp = time();
        $random = substr(md5(uniqid()), 0, 6);
        return $slug . '_' . $timestamp . '_' . $random;
    }
    
    private function slugify($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'image' : $text;
    }
    
    public function generateHtmlCode($urls, $alt_text = '', $size = 'medium', $lazy = true) {
        if (!isset($urls[$size])) {
            $size = 'medium';
        }
        
        $jpeg_url = $urls[$size]['jpeg'];
        $webp_url = $urls[$size]['webp'];
        $loading = $lazy ? 'loading="lazy"' : '';
        
        $html = <<<HTML
<picture>
    <source srcset="{$webp_url}" type="image/webp">
    <img src="{$jpeg_url}" alt="{$alt_text}" {$loading} class="article-image">
</picture>
HTML;
        
        return $html;
    }
    
    public function deleteImage($unique_name) {
        $deleted = [];
        
        foreach ($this->sizes as $size_name => $max_size) {
            $filename = "{$unique_name}_{$size_name}";
            
            $jpeg_path = $this->optimized_dir . $size_name . '/' . $filename . '.jpg';
            if (file_exists($jpeg_path)) {
                unlink($jpeg_path);
                $deleted[] = $jpeg_path;
            }
            
            $webp_path = $this->optimized_dir . 'webp/' . $filename . '.webp';
            if (file_exists($webp_path)) {
                unlink($webp_path);
                $deleted[] = $webp_path;
            }
        }
        
        return [
            'success' => true,
            'deleted_files' => count($deleted),
            'files' => $deleted
        ];
    }
}