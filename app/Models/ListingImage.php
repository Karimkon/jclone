<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ListingImage extends Model
{
    protected $fillable = [
        'listing_id',
        'path',
        'order',
        'type',        // ADD THIS
        'metadata',   // ADD THIS (optional, for video duration, etc.)
        'is_main'
    ];

    protected $casts = [
        'metadata' => 'array'  // ADD THIS
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    // ADD THESE METHODS
    public function getTypeAttribute()
    {
        // Determine type from file extension if not set
        if (!isset($this->attributes['type'])) {
            $path = $this->attributes['path'] ?? '';
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            
            $videoExtensions = ['mp4', 'mov', 'avi', 'wmv', 'flv', 'webm', 'mkv'];
            return in_array($extension, $videoExtensions) ? 'video' : 'image';
        }
        
        return $this->attributes['type'] ?? 'image';
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }

    public function getIsVideoAttribute()
    {
        return $this->type === 'video';
    }

    public function getIsImageAttribute()
    {
        return $this->type === 'image';
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->is_video) {
            // For videos, you might want a thumbnail
            // You could generate/store thumbnails or use a default
            return asset('images/video-thumbnail.png'); // Default thumbnail
        }
        return $this->url;
    }

    public function getDurationAttribute()
    {
        return $this->metadata['duration'] ?? null;
    }
}