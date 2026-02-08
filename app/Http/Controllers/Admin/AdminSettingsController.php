<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminSettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        $backups = Storage::disk('local')->files('backups');
        
        return view('admin.settings.index', compact('settings', 'backups'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'site_email' => 'required|email',
            'site_phone' => 'nullable|string|max:20',
            'site_address' => 'nullable|string|max:500',
            'site_currency' => 'required|string|max:3',
            'site_timezone' => 'required|string|max:100',
            'site_language' => 'required|string|max:10',
            'site_maintenance' => 'boolean',
            'site_maintenance_message' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        foreach ($validator->validated() as $key => $value) {
            $this->updateSetting($key, $value);
        }
        
        // Clear settings cache
        Cache::forget('settings');
        
        return response()->json([
            'success' => true,
            'message' => 'General settings updated successfully'
        ]);
    }

    /**
     * Update email settings
     */
    public function updateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mail_mailer' => 'required|string|max:50',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|max:10',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        foreach ($validator->validated() as $key => $value) {
            $this->updateSetting($key, $value);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Email settings updated successfully'
        ]);
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notify_new_users' => 'boolean',
            'notify_new_orders' => 'boolean',
            'notify_new_vendors' => 'boolean',
            'notify_new_disputes' => 'boolean',
            'notify_new_withdrawals' => 'boolean',
            'notification_email' => 'nullable|email',
            'notification_webhook' => 'nullable|url',
            'notification_telegram' => 'nullable|string',
            'notification_whatsapp' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        foreach ($validator->validated() as $key => $value) {
            $this->updateSetting($key, $value);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully'
        ]);
    }

    /**
     * Update security settings
     */
    public function updateSecurity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'security_login_attempts' => 'required|integer|min:1|max:10',
            'security_lockout_time' => 'required|integer|min:1',
            'security_password_expiry' => 'required|integer|min:0',
            'security_2fa_enabled' => 'boolean',
            'security_ip_whitelist' => 'nullable|string',
            'security_session_timeout' => 'required|integer|min:5',
            'security_log_retention' => 'required|integer|min:1'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        foreach ($validator->validated() as $key => $value) {
            $this->updateSetting($key, $value);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Security settings updated successfully'
        ]);
    }

    /**
     * Create database backup
     */
    public function createBackup(Request $request)
    {
        try {
            $fileName = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
            $filePath = storage_path('app/backups/' . $fileName);
            
            // Ensure directory exists
            if (!Storage::disk('local')->exists('backups')) {
                Storage::disk('local')->makeDirectory('backups');
            }
            
            // Create backup using mysqldump
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.host'),
                config('database.connections.mysql.database'),
                $filePath
            );
            
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception('Backup failed');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'file_name' => $fileName
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup. Please check server logs.'
            ], 500);
        }
    }

    /**
     * View system logs
     */
    public function viewLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return response()->json([
                'success' => false,
                'message' => 'Log file not found'
            ], 404);
        }
        
        $logs = file_get_contents($logFile);
        
        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    /**
     * Clear system logs
     */
    public function clearLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Logs cleared successfully'
        ]);
    }

    /**
     * Update or create setting
     */
    private function updateSetting($key, $value)
    {
        \App\Models\Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}