<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    
    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'event',
        'icon',
        'color',
    ];
    
    protected $casts = [
        'properties' => 'array',
    ];
    
    public function subject()
    {
        return $this->morphTo();
    }
    
    public function causer()
    {
        return $this->morphTo();
    }
    
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
    
    public function scopeForDashboard($query)
    {
        return $query->whereIn('event', [
            'user_registered',
            'vendor_registered',
            'vendor_approved',
            'vendor_rejected',
            'order_created',
            'order_completed',
            'product_listed',
            'product_updated',
            'dispute_opened',
            'dispute_resolved',
            'withdrawal_requested',
            'withdrawal_completed',
        ]);
    }
}