<?php
/**
 * Image Uploader Helper
 * Handles image upload, validation, optimization, and deletion
 */

class ImageUploader {
    private $uploadDir;
    private $uploadPath;
    private $maxFileSize = 5242880; // 5MB
    private $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    
    public function __construct($subFolder = 'products') {
        $this->uploadDir = __DIR__ . '/../../public/uploads/' . $subFolder . '/';
        $this->uploadPath = 'uploads/' . $subFolder . '/';
        
        // Create directory if not exists
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Upload image file
     */
    public function upload($file, $oldImage = null) {
        try {
            // Validate file
            $validation = $this->validate($file);
            if (!$validation['success']) {
                return $validation;
            }
            
            // Delete old image if exists
            if ($oldImage) {
                $this->delete($oldImage);
            }
            
            // Generate unique filename
            $filename = $this->generateFilename($file['name']);
            $filepath = $this->uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return [
                    'success' => false,
                    'message' => 'Failed to move uploaded file'
                ];
            }
            
            // Optimize image
            $this->optimizeImage($filepath);
            
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $this->uploadPath . $filename,
                'url' => $this->uploadPath . $filename
            ];
            
        } catch (Exception $e) {
            error_log('Image upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validate($file) {
        // Check if file uploaded
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return [
                'success' => false,
                'message' => 'No file uploaded'
            ];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'Upload error: ' . $file['error']
            ];
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $sizeMB = round($this->maxFileSize / 1024 / 1024);
            return [
                'success' => false,
                'message' => "File too large. Maximum size is {$sizeMB}MB"
            ];
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Only JPG, PNG, and WEBP are allowed'
            ];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return [
                'success' => false,
                'message' => 'Invalid file extension'
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Generate unique filename
     */
    private function generateFilename($originalName) {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Clean basename
        $basename = preg_replace('/[^a-z0-9]+/', '-', strtolower($basename));
        $basename = trim($basename, '-');
        $basename = substr($basename, 0, 50); // Limit length
        
        // Generate unique name
        $uniqueId = uniqid();
        $timestamp = time();
        
        return "product_{$basename}_{$uniqueId}_{$timestamp}.{$extension}";
    }
    
    /**
     * Optimize image (resize and compress)
     */
    private function optimizeImage($filepath) {
        $imageInfo = getimagesize($filepath);
        if (!$imageInfo) {
            return false;
        }
        
        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Max dimensions
        $maxWidth = 800;
        $maxHeight = 800;
        
        // Skip if image is already small enough
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return true;
        }
        
        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Create image resource
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $source = imagecreatefromjpeg($filepath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($filepath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($filepath);
                break;
            default:
                return false;
        }
        
        if (!$source) {
            return false;
        }
        
        // Create new image
        $destination = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
        }
        
        // Resize
        imagecopyresampled(
            $destination, $source,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $width, $height
        );
        
        // Save optimized image
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($destination, $filepath, 85);
                break;
            case 'image/png':
                imagepng($destination, $filepath, 8);
                break;
            case 'image/webp':
                imagewebp($destination, $filepath, 85);
                break;
        }
        
        // Free memory
        imagedestroy($source);
        imagedestroy($destination);
        
        return true;
    }
    
    /**
     * Delete image file
     */
    public function delete($filename) {
        if (empty($filename)) {
            return false;
        }
        
        // Remove path prefix if exists
        $filename = str_replace($this->uploadPath, '', $filename);
        $filename = basename($filename);
        
        $filepath = $this->uploadDir . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
    
    /**
     * Get full path
     */
    public function getPath($filename) {
        return $this->uploadPath . $filename;
    }
}

