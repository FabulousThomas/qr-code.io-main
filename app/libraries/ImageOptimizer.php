<?php

class ImageOptimizer {
    
    /**
     * Compress and optimize image for web storage
     */
    public static function optimizeImage($imageData, $quality = 80) {
        try {
            $image = imagecreatefromstring($imageData);
            
            if (!$image) {
                throw new Exception("Invalid image data");
            }
            
            // Convert to WebP for better compression
            ob_start();
            imagewebp($image, null, $quality);
            $optimizedData = ob_get_contents();
            ob_end_clean();
            
            imagedestroy($image);
            
            // If WebP is larger than original, use PNG
            if (strlen($optimizedData) > strlen($imageData)) {
                return $imageData;
            }
            
            return $optimizedData;
            
        } catch (Exception $e) {
            return $imageData; // Return original if optimization fails
        }
    }
    
    /**
     * Validate image format and size
     */
    public static function validateImage($imageData, $maxSize = 1048576) { // 1MB default
        $size = strlen($imageData);
        
        if ($size > $maxSize) {
            throw new Exception("Image size exceeds maximum allowed size");
        }
        
        $imageInfo = @getimagesizefromstring($imageData);
        
        if (!$imageInfo) {
            throw new Exception("Invalid image format");
        }
        
        $allowedTypes = ['image/png', 'image/jpeg', 'image/webp'];
        if (!in_array($imageInfo['mime'], $allowedTypes)) {
            throw new Exception("Unsupported image type: " . $imageInfo['mime']);
        }
        
        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime' => $imageInfo['mime'],
            'size' => $size
        ];
    }
    
    /**
     * Generate thumbnail for preview
     */
    public static function generateThumbnail($imageData, $width = 150, $height = 150, $quality = 80) {
        try {
            $image = imagecreatefromstring($imageData);
            
            if (!$image) {
                return false;
            }
            
            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);
            
            // Calculate dimensions
            if ($width == 0 && $height == 0) {
                $width = 150;
                $height = 150;
            }
            
            $ratio = min($width / $originalWidth, $height / $originalHeight);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);
            
            // Create thumbnail
            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            
            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            
            ob_start();
            imagewebp($thumbnail, null, 80);
            $thumbnailData = ob_get_contents();
            ob_end_clean();
            
            imagedestroy($image);
            imagedestroy($thumbnail);
            
            return $thumbnailData;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get image format information
     */
    public static function getImageInfo($imageData) {
        $info = @getimagesizefromstring($imageData);
        
        if (!$info) {
            return false;
        }
        
        return [
            'width' => $info[0],
            'height' => $info[1],
            'type' => $info[2],
            'mime' => $info['mime'],
            'size' => strlen($imageData)
        ];
    }
}
