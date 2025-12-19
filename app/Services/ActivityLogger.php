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
    
    // ================= ORDER-RELATED METHODS =================
    
    public static function logOrderCreated($order)
    {
        return self::log(
            "New order #{$order->order_number}",
            'order_created',
            $order,
            [
                'order_number' => $order->order_number,
                'amount' => $order->total,
                'buyer_id' => $order->buyer_id,
                'vendor_id' => $order->vendor_profile_id
            ],
            'fas fa-shopping-cart',
            'text-blue-500'
        );
    }
    
    public static function logOrderConfirmed($order)
    {
        return self::log(
            "Order confirmed #{$order->order_number}",
            'order_confirmed',
            $order,
            ['order_number' => $order->order_number],
            'fas fa-check-circle',
            'text-green-500'
        );
    }
    
    public static function logOrderProcessing($order)
    {
        return self::log(
            "Order processing #{$order->order_number}",
            'order_processing',
            $order,
            ['order_number' => $order->order_number],
            'fas fa-cog',
            'text-yellow-500'
        );
    }
    
    public static function logOrderShipped($order)
    {
        return self::log(
            "Order shipped #{$order->order_number}",
            'order_shipped',
            $order,
            ['order_number' => $order->order_number],
            'fas fa-shipping-fast',
            'text-orange-500'
        );
    }
    
    public static function logOrderDelivered($order)
    {
        return self::log(
            "Order delivered #{$order->order_number}",
            'order_delivered',
            $order,
            [
                'order_number' => $order->order_number,
                'delivery_time_days' => $order->delivery_time_days ?? 0,
                'delivery_score' => $order->delivery_score ?? 0
            ],
            'fas fa-check-circle',
            'text-green-500'
        );
    }
    
    public static function logOrderCompleted($order)
    {
        return self::log(
            "Order completed #{$order->order_number}",
            'order_completed',
            $order,
            ['order_number' => $order->order_number],
            'fas fa-flag-checkered',
            'text-purple-500'
        );
    }
    
    public static function logOrderCancelled($order)
    {
        return self::log(
            "Order cancelled #{$order->order_number}",
            'order_cancelled',
            $order,
            [
                'order_number' => $order->order_number,
                'cancelled_by' => auth()->user()->name ?? 'System'
            ],
            'fas fa-times-circle',
            'text-red-500'
        );
    }
    
    public static function logOrderRefunded($order)
    {
        return self::log(
            "Order refunded #{$order->order_number}",
            'order_refunded',
            $order,
            ['order_number' => $order->order_number, 'amount' => $order->total],
            'fas fa-undo',
            'text-yellow-500'
        );
    }
    
    // ================= PAYMENT-RELATED METHODS =================
    
    public static function logWalletPayment($order)
    {
        return self::log(
            "Wallet payment for Order #{$order->order_number}",
            'wallet_payment',
            $order,
            [
                'order_number' => $order->order_number,
                'amount' => $order->total,
                'buyer_id' => $order->buyer_id
            ],
            'fas fa-wallet',
            'text-blue-500'
        );
    }
    
    public static function logCardPayment($order)
    {
        return self::log(
            "Card payment for Order #{$order->order_number}",
            'card_payment',
            $order,
            ['order_number' => $order->order_number, 'amount' => $order->total],
            'fas fa-credit-card',
            'text-green-500'
        );
    }
    
    public static function logMobileMoneyPayment($order)
    {
        return self::log(
            "Mobile money payment for Order #{$order->order_number}",
            'mobile_money_payment',
            $order,
            ['order_number' => $order->order_number, 'amount' => $order->total],
            'fas fa-mobile-alt',
            'text-purple-500'
        );
    }
    
    public static function logCODPayment($order)
    {
        return self::log(
            "Cash on Delivery payment for Order #{$order->order_number}",
            'cod_payment',
            $order,
            ['order_number' => $order->order_number, 'amount' => $order->total],
            'fas fa-money-bill-wave',
            'text-green-500'
        );
    }
    
    public static function logRefundRequested($order)
    {
        return self::log(
            "Refund requested for Order #{$order->order_number}",
            'refund_requested',
            $order,
            [
                'order_number' => $order->order_number,
                'amount' => $order->total,
                'buyer_id' => $order->buyer_id
            ],
            'fas fa-undo',
            'text-yellow-500'
        );
    }
    
    public static function logEscrowReleased($order)
    {
        return self::log(
            "Escrow released for Order #{$order->order_number}",
            'escrow_released',
            $order,
            ['order_number' => $order->order_number, 'amount' => $order->escrow->amount ?? 0],
            'fas fa-unlock',
            'text-green-500'
        );
    }
    
    // ================= USER-RELATED METHODS =================
    
    public static function logUserRegistered($user)
    {
        return self::log(
            "New user registered: {$user->name}",
            'user_registered',
            $user,
            ['email' => $user->email, 'role' => $user->role],
            'fas fa-user-plus',
            'text-green-500'
        );
    }
    
    public static function logUserLogin($user)
    {
        return self::log(
            "User logged in: {$user->name}",
            'user_login',
            $user,
            ['email' => $user->email],
            'fas fa-sign-in-alt',
            'text-blue-500'
        );
    }
    
    public static function logUserLogout($user)
    {
        return self::log(
            "User logged out: {$user->name}",
            'user_logout',
            $user,
            ['email' => $user->email],
            'fas fa-sign-out-alt',
            'text-gray-500'
        );
    }
    
    // ================= VENDOR-RELATED METHODS =================
    
    public static function logVendorRegistered($vendor)
    {
        return self::log(
            "New vendor registered: {$vendor->business_name}",
            'vendor_registered',
            $vendor,
            [
                'business_name' => $vendor->business_name,
                'user_id' => $vendor->user_id
            ],
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
    
    public static function logVendorSuspended($vendor)
    {
        return self::log(
            "Vendor suspended: {$vendor->business_name}",
            'vendor_suspended',
            $vendor,
            ['business_name' => $vendor->business_name],
            'fas fa-ban',
            'text-red-500'
        );
    }
    
    // ================= PRODUCT-RELATED METHODS =================
    
    public static function logProductListed($product)
    {
        return self::log(
            "New product listed: {$product->title}",
            'product_listed',
            $product,
            [
                'title' => $product->title,
                'vendor_id' => $product->vendor_profile_id,
                'price' => $product->price
            ],
            'fas fa-upload',
            'text-purple-500'
        );
    }
    
    public static function logProductUpdated($product)
    {
        return self::log(
            "Product updated: {$product->title}",
            'product_updated',
            $product,
            ['title' => $product->title],
            'fas fa-edit',
            'text-yellow-500'
        );
    }
    
    public static function logProductDeleted($product)
    {
        return self::log(
            "Product deleted: {$product->title}",
            'product_deleted',
            $product,
            ['title' => $product->title],
            'fas fa-trash',
            'text-red-500'
        );
    }
    
    // ================= DISPUTE-RELATED METHODS =================
    
    public static function logDisputeOpened($dispute)
    {
        return self::log(
            "New dispute opened #{$dispute->id}",
            'dispute_opened',
            $dispute,
            [
                'order_id' => $dispute->order_id,
                'reason' => $dispute->reason
            ],
            'fas fa-exclamation-circle',
            'text-red-500'
        );
    }
    
    public static function logDisputeResolved($dispute)
    {
        return self::log(
            "Dispute resolved #{$dispute->id}",
            'dispute_resolved',
            $dispute,
            [
                'order_id' => $dispute->order_id,
                'resolution' => $dispute->resolution
            ],
            'fas fa-check-circle',
            'text-green-500'
        );
    }
    
    // ================= WITHDRAWAL-RELATED METHODS =================
    
    public static function logWithdrawalRequested($withdrawal)
    {
        return self::log(
            "Withdrawal requested: $" . number_format($withdrawal->amount, 2),
            'withdrawal_requested',
            $withdrawal,
            [
                'amount' => $withdrawal->amount,
                'vendor' => $withdrawal->vendor->business_name,
                'method' => $withdrawal->payment_method
            ],
            'fas fa-comment-dollar',
            'text-purple-500'
        );
    }
    
    public static function logWithdrawalCompleted($withdrawal)
    {
        return self::log(
            "Withdrawal completed: $" . number_format($withdrawal->amount, 2),
            'withdrawal_completed',
            $withdrawal,
            ['amount' => $withdrawal->amount],
            'fas fa-check-circle',
            'text-green-500'
        );
    }
    
    // ================= HELPER METHODS =================
    
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
    
    public static function timeAgo($date)
    {
        return self::formatActivityTime($date);
    }


}