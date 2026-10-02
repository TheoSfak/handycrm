<?php
/**
 * Photo & Image Processing Service
 * Centralizes image resizing, optimization, and format handling.
 */

class PhotoService {
    /**
     * Resize and optimize an image file
     *
     * @param string $sourcePath Absolute or relative path to source image
     * @param string $destinationPath Path where resized image should be saved
     * @param int|null $maxWidth Max width in pixels (defaults to IMAGE_MAX_WIDTH or 1920)
     * @param int|null $maxHeight Max height in pixels (defaults to IMAGE_MAX_HEIGHT or 1080)
     * @param int|null $quality JPEG/WEBP quality 1-100 (defaults to IMAGE_QUALITY or 85)
     * @param bool $keepTransparency Whether to preserve alpha channel (e.g. for PNG logos)
     * @return bool True on success, false on failure
     */
    public static function resize(
        string $sourcePath,
        string $destinationPath,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        ?int $quality = null,
        bool $keepTransparency = false
    ): bool {
        if (!file_exists($sourcePath)) {
            return false;
        }

        $maxWidth = $maxWidth ?? (defined('IMAGE_MAX_WIDTH') ? IMAGE_MAX_WIDTH : 1920);
        $maxHeight = $maxHeight ?? (defined('IMAGE_MAX_HEIGHT') ? IMAGE_MAX_HEIGHT : 1080);
        $quality = $quality ?? (defined('IMAGE_QUALITY') ? IMAGE_QUALITY : 85);

        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        list($origWidth, $origHeight, $imageType) = $imageInfo;

        if ($origWidth <= 0 || $origHeight <= 0) {
            return false;
        }

        // Calculate aspect ratio
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);

        if ($ratio >= 1) {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        } else {
            $newWidth = (int)round($origWidth * $ratio);
            $newHeight = (int)round($origHeight * $ratio);
        }

        // Ensure minimum 1px dimension
        $newWidth = max(1, $newWidth);
        $newHeight = max(1, $newHeight);

        // Load image resource
        $sourceImage = null;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = @imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = @imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = @imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $sourceImage = @imagecreatefromwebp($sourcePath);
                }
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        if (!$newImage) {
            imagedestroy($sourceImage);
            return false;
        }

        if ($keepTransparency) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Ensure destination directory exists
        $destDir = dirname($destinationPath);
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0755, true);
        }

        $result = false;
        if ($keepTransparency) {
            $result = imagepng($newImage, $destinationPath, 9);
        } else {
            $result = imagejpeg($newImage, $destinationPath, $quality);
        }

        imagedestroy($sourceImage);
        imagedestroy($newImage);

        return $result;
    }
}
