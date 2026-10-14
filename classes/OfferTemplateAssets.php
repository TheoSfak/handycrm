<?php
/**
 * Embedded Asset Provider for Maintenance Offers
 * Guarantees zero-dependency fallback for the approved Ecowatt hero photo.
 */
class OfferTemplateAssets {
    /**
     * Resolves and guarantees the base64 data URI of the approved hero mockup photo.
     * Strictly rejects any legacy photo <= 65,000 bytes (old oil transformer was 58,793 bytes).
     */
    public static function getHeroPhotoBase64(): string {
        $candTemplates1 = __DIR__ . '/../templates/docx_images/substation_hero_mockup.jpeg';
        $candTemplates2 = __DIR__ . '/../templates/docx_images/image2.jpeg';
        $candUploads1   = __DIR__ . '/../uploads/template_images/substation_hero_mockup.jpeg';
        $candUploads2   = __DIR__ . '/../uploads/template_images/image2.jpeg';

        foreach ([$candTemplates1, $candTemplates2, $candUploads1, $candUploads2] as $path) {
            if (file_exists($path) && filesize($path) > 65000) {
                $raw = @file_get_contents($path);
                if ($raw !== false && strlen($raw) > 65000) {
                    return 'data:image/jpeg;base64,' . base64_encode($raw);
                }
            }
        }

        $txtFallback = __DIR__ . '/../templates/docx_images/substation_hero_base64.txt';
        if (file_exists($txtFallback)) {
            $b64 = trim(@file_get_contents($txtFallback));
            if ($b64 !== '') {
                return 'data:image/jpeg;base64,' . $b64;
            }
        }

        return '';
    }

    /**
     * Ensures the approved mockup photo exists on disk and is self-healed into uploads/.
     * Returns the verified absolute path to an image file with size > 65,000 bytes.
     */
    public static function ensureHeroPhotoOnDisk(): string {
        $uploadsDir = __DIR__ . '/../uploads/template_images';
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0755, true);
        }

        $candUploads1 = $uploadsDir . '/substation_hero_mockup.jpeg';
        $candUploads2 = $uploadsDir . '/image2.jpeg';

        // Purge old 58KB oil transformer from uploads if present
        foreach ([$candUploads1, $candUploads2] as $f) {
            if (file_exists($f) && filesize($f) <= 65000) {
                @unlink($f);
            }
        }

        $candTemplates1 = __DIR__ . '/../templates/docx_images/substation_hero_mockup.jpeg';
        $candTemplates2 = __DIR__ . '/../templates/docx_images/image2.jpeg';
        $validSource = '';

        foreach ([$candTemplates1, $candTemplates2, $candUploads1, $candUploads2] as $path) {
            if (file_exists($path) && filesize($path) > 65000) {
                $validSource = $path;
                break;
            }
        }

        if ($validSource !== '' && file_exists($validSource)) {
            if (!file_exists($candUploads1) || filesize($candUploads1) <= 65000) {
                @copy($validSource, $candUploads1);
            }
            if (!file_exists($candUploads2) || filesize($candUploads2) <= 65000) {
                @copy($validSource, $candUploads2);
            }
            return $validSource;
        }

        // Restore from base64 text if disk files were missing
        $txtFallback = __DIR__ . '/../templates/docx_images/substation_hero_base64.txt';
        if (file_exists($txtFallback)) {
            $b64 = trim(@file_get_contents($txtFallback));
            if ($b64 !== '') {
                $binary = base64_decode($b64);
                if ($binary && strlen($binary) > 65000) {
                    file_put_contents($candUploads1, $binary);
                    file_put_contents($candUploads2, $binary);
                    $templatesDir = __DIR__ . '/../templates/docx_images';
                    if (is_dir($templatesDir)) {
                        @file_put_contents($candTemplates1, $binary);
                        @file_put_contents($candTemplates2, $binary);
                    }
                    return $candUploads1;
                }
            }
        }

        return '';
    }
}
