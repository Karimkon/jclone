<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log($description, $event = null, Model $subject = null, $properties = [], $icon = null, $color = null)
    {
        $log = ActivityLog::create([
            'log_name' => 'admin_dashboard',
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
            'causer_id' => auth()->id(),
            'properties' => $properties,
            'event' => $event,
            'icon' => $icon,
            'color' => $color,
        ]);
        
        return $log;
    }
    
    public static function logUserRegistered($user)
    {
        return self::log(
            "New user registered: {$user->name}",
            'user_registered',
            $user,
            ['email' => $user->email],
            'fas fa-user-plus',
            'text-green-500'
        );
    }
    
    public static function logVendorRegistered($vendor)
    {
        return self::log(
            "New vendor registered: {$vendor->business_name}",
            'vendor_registered',
            $vendor,
            ['business_name' => $vendor->business_name],
            'fas fa-store',
            'text-blue-500'
        );
    }
    
    public static function logVendorApproved($vendor)
    {
        return self::log(
            "Vendor approved: {$vendor->business_name}",
            'vendor_approved',
            $vendor,
            ['business_name' => $vendor->business_name],
            'fas fa-check-circle',
            'text-green-500'
        );
    }
    
    public static function logVendorRejected($vendor)
    {
        return self::log(
            "Vendor rejected: {$vendor->business_name}",
            'vendor_rejected',
            $vendor,
            ['business_name' => $vendor->business_name],
            'fas fa-times-circle',
            'text-red-500'
        );
    }
    
    public static function logOrderCreated($order)
    {
        return self::log(
            "New order #{$order->order_number}",
            'order_created',
            $order,
            ['order_number' => $order->order_number, 'amount' => $order->total_amount],
            'fas fa-shopping-cart',
            'text-blue-500'
        );
    }
    
    public static function logOrderCompleted($order)
    {
        return self::log(
            "Order completed #{$order->order_number}",
            'order_completed',
            $order,
            ['order_number' => $order->order_number],
            'fas fa-check-circle',
            'text-green-500'
        );
    }
    
    public static function logProductListed($product)
    {
        return self::log(
            "New product listed: {$product->title}",
            'product_listed',
            $product,
            ['title' => $product->title],
            'fas fa-upload',
            'text-purple-500'
        );
    }
    
    public static function logDisputeOpened($dispute)
    {
        return self::log(
            "New dispute opened #{$dispute->id}",
            'dispute_opened',
            $dispute,
            ['order_id' => $dispute->order_id],
            'fas fa-exclamation-circle',
            'text-red-500'
        );
    }
    
    public static function logWithdrawalRequested($withdrawal)
    {
        return self::log(
            "Withdrawal requested: $" . number_format($withdrawal->amount, 2),
            'withdrawal_requested',
            $withdrawal,
            ['amount' => $withdrawal->amount, 'vendor' => $withdrawal->vendor->business_name],
            'fas fa-comment-dollar',
            'text-purple-500'
        );
    }
    
    public static function getRecentActivities($limit = 10)
    {
        return ActivityLog::forDashboard()
            ->recent($limit)
            ->get();
    }
    
    public static function formatActivityTime($date)
    {
        $now = now();
        $diff = $date->diff($now);
        
        if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        return 'Just now';
    }
}