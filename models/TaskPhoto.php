<?php
/**
 * TaskPhoto Model
 * Handles task photo gallery management
 */

require_once 'classes/BaseModel.php';

class TaskPhoto extends BaseModel {
    protected $table = 'task_photos';
    
    /**
     * Get all photos for a task
     * 
     * @param int $taskId Task ID
     * @param string|null $type Filter by photo type (before, after, during, issue, other)
     * @return array
     */
    public function getByTask($taskId, $type = null) {
        $sql = "SELECT tp.*, u.username, u.first_name, u.last_name 
                FROM {$this->table} tp
                LEFT JOIN users u ON tp.uploaded_by = u.id
                WHERE tp.task_id = ?";
        
        $params = [$taskId];
        
        if ($type) {
            $sql .= " AND tp.photo_type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY tp.sort_order ASC, tp.created_at ASC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get photos grouped by type
     * 
     * @param int $taskId Task ID
     * @return array
     */
    public function getGroupedByType($taskId) {
        $photos = $this->getByTask($taskId);
        
        $grouped = [
            'before' => [],
            'after' => [],
            'during' => [],
            'issue' => [],
            'other' => []
        ];
        
        foreach ($photos as $photo) {
            $grouped[$photo['photo_type']][] = $photo;
        }
        
        return $grouped;
    }
    
    /**
     * Upload and save photo
     * 
     * @param int $taskId Task ID
     * @param array $file $_FILES array element
     * @param string $type Photo type
     * @param string|null $caption Caption
     * @param int $uploadedBy User ID
     * @return int|false Photo ID or false on failure
     */
    public function uploadPhoto($taskId, $file, $type, $caption, $uploadedBy) {
        // Validate file
        if (!$this->validatePhoto($file)) {
            return false;
        }
        
        // Generate unique filename (always save as JPG for better compression)
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'task_' . $taskId . '_' . time() . '_' . uniqid() . '.jpg';
        
        // Create upload directory if not exists
        $uploadDir = __DIR__ . '/../uploads/task_photos/' . date('Y') . '/' . date('m');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filePath = $uploadDir . '/' . $filename;
        $relativeePath = 'uploads/task_photos/' . date('Y') . '/' . date('m') . '/' . $filename;
        
        // Resize and optimize image (max 1920x1080, 85% quality)
        if (!$this->resizeImage($file['tmp_name'], $filePath, 1920, 1080, 85)) {
            return false;
        }
        
        // Get file size after resize
        $fileSize = filesize($filePath);
        
        // Save to database
        $sql = "INSERT INTO {$this->table} 
                (task_id, filename, original_filename, file_path, file_size, mime_type, 
                 photo_type, caption, uploaded_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->execute($sql, [
            $taskId,
            $filename,
            $file['name'],
            $relativeePath,
            $fileSize,
            $file['type'],
            $type,
            $caption,
            $uploadedBy
        ]);
        
        return $stmt ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Validate photo file
     * 
     * @param array $file $_FILES array element
     * @return bool
     */
    private function validatePhoto($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Check file size (max 10MB, will be resized)
        if ($file['size'] > 10 * 1024 * 1024) {
            return false;
        }
        
        // Check mime type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        // Verify it's actually an image
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Resize and optimize image
     * Max dimensions: 1920x1080, JPEG quality: 85%
     * 
     * @param string $sourcePath Source file path
     * @param string $destinationPath Destination file path
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @param int $quality JPEG quality (0-100)
     * @return bool
     */
    private function resizeImage($sourcePath, $destinationPath, $maxWidth = 1920, $maxHeight = 1080, $quality = 85) {
        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }
        
        list($origWidth, $origHeight, $imageType) = $imageInfo;
        
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
        
        // If image is already smaller, still process for optimization
        if ($ratio >= 1) {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        } else {
            $newWidth = round($origWidth * $ratio);
            $newHeight = round($origHeight * $ratio);
        }
        
        // Create image resource from source
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG
        if ($imageType == IMAGETYPE_PNG) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        
        // Always save as JPEG for smaller file size (web photos don't need transparency)
        $result = imagejpeg($newImage, $destinationPath, $quality);
        
        // Free memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return $result;
    }
    
    /**
     * Delete photo
     * 
     * @param int $photoId Photo ID
     * @return bool
     */
    public function deletePhoto($photoId) {
        // Get photo info
        $photo = $this->find($photoId);
        if (!$photo) {
            return false;
        }
        
        // Delete file
        $filePath = __DIR__ . '/../' . $photo['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete from database
        return $this->delete($photoId);
    }
    
    /**
     * Update photo details
     * 
     * @param int $photoId Photo ID
     * @param array $data Data to update (photo_type, caption, sort_order)
     * @return bool
     */
    public function updatePhoto($photoId, $data) {
        $allowed = ['photo_type', 'caption', 'sort_order'];
        $updates = [];
        $params = [];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $photoId;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $stmt = $this->db->execute($sql, $params);
        return $stmt !== false;
    }
    
    /**
     * Get photo count by type for a task
     * 
     * @param int $taskId Task ID
     * @return array
     */
    public function getCountByType($taskId) {
        $sql = "SELECT photo_type, COUNT(*) as count 
                FROM {$this->table} 
                WHERE task_id = ? 
                GROUP BY photo_type";
        
        $results = $this->query($sql, [$taskId]);
        
        $counts = [
            'before' => 0,
            'after' => 0,
            'during' => 0,
            'issue' => 0,
            'other' => 0,
            'total' => 0
        ];
        
        foreach ($results as $row) {
            $counts[$row['photo_type']] = (int)$row['count'];
            $counts['total'] += (int)$row['count'];
        }
        
        return $counts;
    }
    
    /**
     * Create thumbnail
     * 
     * @param string $filePath Path to original image
     * @param int $size Thumbnail size
     * @return string|false Path to thumbnail or false
     */
    public function createThumbnail($filePath, $size = 300) {
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // Calculate dimensions (square crop)
        $cropSize = min($width, $height);
        $cropX = ($width - $cropSize) / 2;
        $cropY = ($height - $cropSize) / 2;
        
        // Load image
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filePath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($filePath);
                break;
            default:
                return false;
        }
        
        // Create thumbnail
        $thumb = imagecreatetruecolor($size, $size);
        
        // Preserve transparency
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
            imagefilledrectangle($thumb, 0, 0, $size, $size, $transparent);
        }
        
        // Crop and resize
        imagecopyresampled($thumb, $source, 0, 0, $cropX, $cropY, $size, $size, $cropSize, $cropSize);
        
        // Save thumbnail
        $thumbPath = str_replace('.', '_thumb.', $filePath);
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $thumbPath, 80);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumb, $thumbPath, 7);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumb, $thumbPath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($thumb, $thumbPath, 80);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($thumb);
        
        return $thumbPath;
    }
}
