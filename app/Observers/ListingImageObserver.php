<?php

namespace App\Observers;

use App\Models\ListingImage;
use Illuminate\Support\Facades\Storage;

class ListingImageObserver
{
    /**
     * Handle the ListingImage "created" event.
     * Automatically applies watermark to new images.
     */
    public function created(ListingImage $image): void
    {
        // Skip if not an image (e.g., video)
        if ($image->type === 'video') {
            return;
        }

        // Get the full path
        $fullPath = Storage::disk('public')->path($image->path);

        if (!file_exists($fullPath)) {
            return;
        }

        // Get vendor name for watermark
        $vendorName = 'BebaMart';
        if ($image->listing && $image->listing->vendor) {
            $vendorName = $image->listing->vendor->business_name
                ?? ($image->listing->vendor->user->name ?? 'BebaMart');
        }

        // Apply watermark
        $this->addWatermark($fullPath, $vendorName);
    }

    /**
     * Add watermark to image
     */
    private function addWatermark(string $fullPath, string $vendorName): bool
    {
        // Check GD extension
        if (!extension_loaded('gd')) {
            \Log::warning('GD extension not loaded - watermark skipped');
            return false;
        }

        $imageInfo = @getimagesize($fullPath);
        if (!$imageInfo) {
            return false;
        }

        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Create image resource
        $image = match($mimeType) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($fullPath),
            'image/png' => @imagecreatefrompng($fullPath),
            'image/webp' => @imagecreatefromwebp($fullPath),
            default => null
        };

        if (!$image) {
            return false;
        }

        // Watermark text
        $watermarkText = $vendorName . ' | bebamart.com';
        if (strlen($watermarkText) > 28) {
            $vendorName = substr($vendorName, 0, 10) . '...';
            $watermarkText = $vendorName . ' | bebamart.com';
        }

        // Font size 2 = small, subtle watermark
        $fontSize = 2;
        $fontWidth = imagefontwidth($fontSize);
        $fontHeight = imagefontheight($fontSize);
        $textWidth = strlen($watermarkText) * $fontWidth;
        $textHeight = $fontHeight;

        // Position: bottom-right
        $padding = 6;
        $x = $width - $textWidth - $padding;
        $y = $height - $textHeight - $padding;

        if ($x < $padding) $x = $padding;

        // Colors
        $bgColor = imagecolorallocatealpha($image, 0, 0, 0, 70);
        $textColor = imagecolorallocate($image, 255, 255, 255);

        // Draw background
        imagefilledrectangle($image, $x - 3, $y - 2, $x + $textWidth + 3, $y + $textHeight + 2, $bgColor);

        // Draw text
        imagestring($image, $fontSize, $x, $y, $watermarkText, $textColor);

        // Save
        $result = match($mimeType) {
            'image/jpeg', 'image/jpg' => imagejpeg($image, $fullPath, 92),
            'image/png' => imagepng($image, $fullPath, 8),
            'image/webp' => imagewebp($image, $fullPath, 92),
            default => false
        };

        imagedestroy($image);
        return $result;
    }
}
