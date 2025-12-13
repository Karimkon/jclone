<?php
// ==========================================
// FILE: app/Models/ServiceCategory.php
// ==========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceCategory extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'image', 
        'parent_id', 'type', 'is_active', 'sort_order'
    ];

    protected $casts = ['is_active' => 'boolean'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($cat) {
            if (empty($cat->slug)) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }

    public function parent() { return $this->belongsTo(ServiceCategory::class, 'parent_id'); }
    public function children() { return $this->hasMany(ServiceCategory::class, 'parent_id'); }
    public function jobs() { return $this->hasMany(JobListing::class); }
    public function services() { return $this->hasMany(VendorService::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeParents($q) { return $q->whereNull('parent_id'); }
    public function scopeForJobs($q) { return $q->whereIn('type', ['job', 'both']); }
    public function scopeForServices($q) { return $q->whereIn('type', ['service', 'both']); }
}