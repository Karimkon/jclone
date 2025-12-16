@extends('layouts.admin')

@section('title', 'System Settings')
@section('page-title', 'System Settings')
@section('page-description', 'Configure platform settings and preferences')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-white">
                <i class="fas fa-home mr-2"></i>
                Dashboard
            </a>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400"></i>
                <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400">Settings</span>
            </div>
        </li>
    </ol>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Settings Navigation -->
    <div class="bg-white dark:bg-dark-800 rounded-xl shadow border border-gray-200 dark:border-dark-700 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-dark-700">
            <div class="flex overflow-x-auto">
                <button onclick="showTab('general')"
                        class="tab-btn flex-1 px-6 py-4 text-sm font-medium border-b-2 transition"
                        id="general-tab">
                    <i class="fas fa-cog mr-2"></i> General
                </button>
                <button onclick="showTab('email')"
                        class="tab-btn flex-1 px-6 py-4 text-sm font-medium border-b-2 transition"
                        id="email-tab">
                    <i class="fas fa-envelope mr-2"></i> Email
                </button>
                <button onclick="showTab('notifications')"
                        class="tab-btn flex-1 px-6 py-4 text-sm font-medium border-b-2 transition"
                        id="notifications-tab">
                    <i class="fas fa-bell mr-2"></i> Notifications
                </button>
                <button onclick="showTab('security')"
                        class="tab-btn flex-1 px-6 py-4 text-sm font-medium border-b-2 transition"
                        id="security-tab">
                    <i class="fas fa-shield-alt mr-2"></i> Security
                </button>
                <button onclick="showTab('backup')"
                        class="tab-btn flex-1 px-6 py-4 text-sm font-medium border-b-2 transition"
                        id="backup-tab">
                    <i class="fas fa-database mr-2"></i> Backup
                </button>
                <button onclick="showTab('logs')"
                        class="tab-btn flex-1 px-6 py-4 text-sm font-medium border-b-2 transition"
                        id="logs-tab">
                    <i class="fas fa-file-alt mr-2"></i> Logs
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div id="tab-content" class="space-y-6">
        <!-- General Settings -->
        <div id="general-content" class="tab-content">
            <div class="bg-white dark:bg-dark-800 rounded-xl shadow border border-gray-200 dark:border-dark-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">General Settings</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Configure basic platform settings</p>
                </div>
                <div class="p-6">
                    <form id="generalForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Site Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="site_name" value="{{ $settings['site_name'] ?? config('app.name') }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Site Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="site_email" value="{{ $settings['site_email'] ?? 'admin@example.com' }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Site Phone
                                </label>
                                <input type="text" name="site_phone" value="{{ $settings['site_phone'] ?? '' }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Site Currency <span class="text-red-500">*</span>
                                </label>
                                <select name="site_currency"
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                    <option value="UGX" {{ ($settings['site_currency'] ?? 'UGX') == 'UGX' ? 'selected' : '' }}>UGX - Ugandan Shilling</option>
                                    <option value="USD" {{ ($settings['site_currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ ($settings['site_currency'] ?? '') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="GBP" {{ ($settings['site_currency'] ?? '') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                    <option value="KES" {{ ($settings['site_currency'] ?? '') == 'KES' ? 'selected' : '' }}>KES - Kenyan Shilling</option>
                                    <option value="TZS" {{ ($settings['site_currency'] ?? '') == 'TZS' ? 'selected' : '' }}>TZS - Tanzanian Shilling</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Timezone <span class="text-red-500">*</span>
                                </label>
                                <select name="site_timezone"
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                    @foreach(timezone_identifiers_list() as $timezone)
                                        <option value="{{ $timezone }}" {{ ($settings['site_timezone'] ?? config('app.timezone')) == $timezone ? 'selected' : '' }}>
                                            {{ $timezone }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Language <span class="text-red-500">*</span>
                                </label>
                                <select name="site_language"
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                    <option value="en" {{ ($settings['site_language'] ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                                    <option value="fr" {{ ($settings['site_language'] ?? '') == 'fr' ? 'selected' : '' }}>French</option>
                                    <option value="sw" {{ ($settings['site_language'] ?? '') == 'sw' ? 'selected' : '' }}>Swahili</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Site Description
                            </label>
                            <textarea name="site_description" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">{{ $settings['site_description'] ?? '' }}</textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Site Address
                            </label>
                            <textarea name="site_address" rows="2"
                                      class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">{{ $settings['site_address'] ?? '' }}</textarea>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-900 rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Maintenance Mode</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Take the site offline for maintenance</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="site_maintenance" value="1" class="sr-only peer"
                                       {{ ($settings['site_maintenance'] ?? false) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                            </label>
                        </div>
                        
                        <div id="maintenanceMessage" class="{{ ($settings['site_maintenance'] ?? false) ? '' : 'hidden' }}">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Maintenance Message
                            </label>
                            <textarea name="site_maintenance_message" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">{{ $settings['site_maintenance_message'] ?? 'We are currently performing maintenance. We will be back shortly.' }}</textarea>
                        </div>
                        
                        <div id="generalErrors" class="hidden p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"></div>
                        
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-700">
                            <button type="submit"
                                    class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                                <i class="fas fa-save"></i>
                                <span>Save General Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Email Settings -->
        <div id="email-content" class="tab-content hidden">
            <div class="bg-white dark:bg-dark-800 rounded-xl shadow border border-gray-200 dark:border-dark-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Email Settings</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Configure email server settings</p>
                </div>
                <div class="p-6">
                    <form id="emailForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Mail Driver <span class="text-red-500">*</span>
                                </label>
                                <select name="mail_mailer"
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                    <option value="smtp" {{ ($settings['mail_mailer'] ?? 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                    <option value="mailgun" {{ ($settings['mail_mailer'] ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                    <option value="ses" {{ ($settings['mail_mailer'] ?? '') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                    <option value="sendmail" {{ ($settings['mail_mailer'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    SMTP Host <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="mail_host" value="{{ $settings['mail_host'] ?? 'smtp.mailtrap.io' }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    SMTP Port <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="mail_port" value="{{ $settings['mail_port'] ?? '2525' }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Encryption
                                </label>
                                <select name="mail_encryption"
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                    <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ ($settings['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="" {{ empty($settings['mail_encryption'] ?? '') ? 'selected' : '' }}>None</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Username
                                </label>
                                <input type="text" name="mail_username" value="{{ $settings['mail_username'] ?? '' }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Password
                                </label>
                                <input type="password" name="mail_password" value="{{ $settings['mail_password'] ?? '' }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    From Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] ?? 'noreply@example.com' }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    From Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] ?? config('app.name') }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                        </div>
                        
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 text-lg mt-0.5"></i>
                                <div>
                                    <h4 class="font-medium text-blue-900 dark:text-blue-300">Testing Email Configuration</h4>
                                    <p class="text-sm text-blue-800 dark:text-blue-400 mt-1">
                                        After saving these settings, you can test the email configuration by sending a test email.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div id="emailErrors" class="hidden p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"></div>
                        
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-700">
                            <button type="button" onclick="testEmail()"
                                    class="px-5 py-2.5 border border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-dark-700 transition flex items-center gap-2">
                                <i class="fas fa-paper-plane"></i>
                                <span>Test Email</span>
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                                <i class="fas fa-save"></i>
                                <span>Save Email Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Notification Settings -->
        <div id="notifications-content" class="tab-content hidden">
            <div class="bg-white dark:bg-dark-800 rounded-xl shadow border border-gray-200 dark:border-dark-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notification Settings</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Configure notification preferences</p>
                </div>
                <div class="p-6">
                    <form id="notificationsForm" class="space-y-6">
                        <!-- Notification Types -->
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-4">Notification Types</h4>
                            <div class="space-y-4">
                                @foreach([
                                    'notify_new_users' => 'New User Registrations',
                                    'notify_new_orders' => 'New Orders',
                                    'notify_new_vendors' => 'New Vendor Applications',
                                    'notify_new_disputes' => 'New Disputes',
                                    'notify_new_withdrawals' => 'New Withdrawal Requests'
                                ] as $key => $label)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-900 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $label }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Receive notifications for {{ strtolower($label) }}</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="{{ $key }}" value="1" class="sr-only peer"
                                               {{ ($settings[$key] ?? true) ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Notification Channels -->
                        <div class="pt-6 border-t border-gray-200 dark:border-dark-700">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-4">Notification Channels</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Email Address
                                    </label>
                                    <input type="email" name="notification_email" value="{{ $settings['notification_email'] ?? '' }}"
                                           class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                                           placeholder="notifications@example.com">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Webhook URL
                                    </label>
                                    <input type="url" name="notification_webhook" value="{{ $settings['notification_webhook'] ?? '' }}"
                                           class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                                           placeholder="https://hooks.slack.com/services/...">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Telegram Bot Token
                                    </label>
                                    <input type="text" name="notification_telegram" value="{{ $settings['notification_telegram'] ?? '' }}"
                                           class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                                           placeholder="1234567890:ABCDEFGHIJKLMNOPQRSTUVWXYZ">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        WhatsApp Number
                                    </label>
                                    <input type="text" name="notification_whatsapp" value="{{ $settings['notification_whatsapp'] ?? '' }}"
                                           class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                                           placeholder="+1234567890">
                                </div>
                            </div>
                        </div>
                        
                        <div id="notificationsErrors" class="hidden p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"></div>
                        
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-700">
                            <button type="submit"
                                    class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                                <i class="fas fa-save"></i>
                                <span>Save Notification Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Security Settings -->
        <div id="security-content" class="tab-content hidden">
            <div class="bg-white dark:bg-dark-800 rounded-xl shadow border border-gray-200 dark:border-dark-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Security Settings</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Configure platform security settings</p>
                </div>
                <div class="p-6">
                    <form id="securityForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Max Login Attempts <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="security_login_attempts" min="1" max="10"
                                       value="{{ $settings['security_login_attempts'] ?? 5 }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Number of failed attempts before lockout</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Lockout Time (minutes) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="security_lockout_time" min="1"
                                       value="{{ $settings['security_lockout_time'] ?? 15 }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Lockout duration after max attempts</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Password Expiry (days) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="security_password_expiry" min="0"
                                       value="{{ $settings['security_password_expiry'] ?? 90 }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">0 = never expires</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Session Timeout (minutes) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="security_session_timeout" min="5"
                                       value="{{ $settings['security_session_timeout'] ?? 30 }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Inactivity timeout</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Log Retention (days) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="security_log_retention" min="1"
                                       value="{{ $settings['security_log_retention'] ?? 30 }}"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Days to keep security logs</p>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-900 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">Two-Factor Authentication</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Require 2FA for admin access</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="security_2fa_enabled" value="1" class="sr-only peer"
                                           {{ ($settings['security_2fa_enabled'] ?? false) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                IP Whitelist
                            </label>
                            <textarea name="security_ip_whitelist" rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition resize-none"
                                      placeholder="Enter one IP address per line&#10;Example:&#10;192.168.1.1&#10;10.0.0.1">{{ $settings['security_ip_whitelist'] ?? '' }}</textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave empty to allow all IPs. One IP per line.</p>
                        </div>
                        
                        <div id="securityErrors" class="hidden p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"></div>
                        
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-700">
                            <button type="submit"
                                    class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                                <i class="fas fa-save"></i>
                                <span>Save Security Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Backup Settings -->
        <div id="backup-content" class="tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Create Backup -->
                <div class="bg-white dark:bg-dark-800 rounded-xl shadow border border-gray-200 dark:border-dark-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Create Backup</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Create a new database backup</p>
                    </div>
                    <div class="p-6">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-database text-green-600 dark:text-green-400 text-2xl"></i>
                            </div>
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Database Backup</h4>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">Create a full backup of your database including all tables and data.</p>
                            
                            <button onclick="createBackup()"
                                    class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition flex items-center justify-center gap-2">
                                <i class="fas fa-plus-circle"></i>
                                <span>Create New Backup</span>
                            </button>
                            
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                                <i class="fas fa-info-circle mr-1"></i>
                                Backups are stored locally in the backups directory
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Existing Backups -->
                <div class="bg-white dark:bg-dark-800 rounded-xl shadow border border-gray-200 dark:border-dark-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Existing Backups</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Manage your database backups</p>
                    </div>
                    <div class="p-6">
                        @if(count($backups) > 0)
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($backups as $backup)
                            @php
                                $filename = basename($backup);
                                $filesize = Storage::size($backup);
                                $date = Storage::lastModified($backup);
                            @endphp
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-900 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-archive text-blue-600 dark:text-blue-400"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $filename }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ date('M d, Y H:i', $date) }} • {{ round($filesize / 1024, 2) }} KB
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button onclick="downloadBackup('{{ $filename }}')"
                                            class="p-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button onclick="deleteBackup('{{ $filename }}')"
                                            class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-dark-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-database text-gray-400 dark:text-gray-500 text-xl"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">No backups found</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- System Logs -->
        <div id="logs-content" class="tab-content hidden">
            <div class="bg-white dark:bg-dark-800 rounded-xl shadow border border-gray-200 dark:border-dark-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">System Logs</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">View and manage system logs</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="refreshLogs()"
                                class="px-4 py-2 bg-gray-200 dark:bg-dark-700 hover:bg-gray-300 dark:hover:bg-dark-600 text-gray-800 dark:text-gray-200 rounded-lg font-medium transition flex items-center gap-2">
                            <i class="fas fa-sync-alt"></i>
                            <span>Refresh</span>
                        </button>
                        <button onclick="clearLogs()"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                            <i class="fas fa-trash"></i>
                            <span>Clear Logs</span>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="bg-gray-900 dark:bg-black rounded-lg p-4 font-mono text-sm">
                        <pre id="logContent" class="text-green-400 h-96 overflow-y-auto whitespace-pre-wrap">Loading logs...</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tab management
    let currentTab = 'general';
    
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });
        
        // Remove active from all tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-primary-600', 'text-primary-600', 'dark:text-primary-400');
            btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-300');
        });
        
        // Show selected tab
        document.getElementById(`${tabName}-content`).classList.remove('hidden');
        document.getElementById(`${tabName}-tab`).classList.add('border-primary-600', 'text-primary-600', 'dark:text-primary-400');
        document.getElementById(`${tabName}-tab`).classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-300');
        
        currentTab = tabName;
        
        // Load logs if logs tab
        if (tabName === 'logs') {
            loadLogs();
        }
    }
    
    // Initialize tabs
    document.addEventListener('DOMContentLoaded', function() {
        // Set first tab as active
        document.getElementById('general-tab').classList.add('border-primary-600', 'text-primary-600', 'dark:text-primary-400');
        document.getElementById('general-tab').classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-300');
        
        // Maintenance mode toggle
        const maintenanceToggle = document.querySelector('input[name="site_maintenance"]');
        const maintenanceMessage = document.getElementById('maintenanceMessage');
        
        if (maintenanceToggle) {
            maintenanceToggle.addEventListener('change', function() {
                if (this.checked) {
                    maintenanceMessage.classList.remove('hidden');
                } else {
                    maintenanceMessage.classList.add('hidden');
                }
            });
        }
    });
    
    // Load system logs
    function loadLogs() {
        const logContent = document.getElementById('logContent');
        logContent.textContent = 'Loading logs...';
        
        fetch('{{ route("admin.settings.logs") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    logContent.textContent = data.logs || 'No logs found';
                } else {
                    logContent.textContent = 'Error loading logs: ' + data.message;
                }
            })
            .catch(error => {
                logContent.textContent = 'Error loading logs: ' + error;
            });
    }
    
    function refreshLogs() {
        loadLogs();
        
        Swal.fire({
            icon: 'success',
            title: 'Refreshed!',
            text: 'Logs refreshed successfully',
            timer: 1000,
            showConfirmButton: false
        });
    }
    
    function clearLogs() {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will clear all system logs. This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, clear logs!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("admin.settings.logs.clear") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cleared!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        loadLogs();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to clear logs',
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while clearing logs',
                    });
                });
            }
        });
    }
    
    // Create database backup
    function createBackup() {
        Swal.fire({
            title: 'Creating Backup',
            text: 'Please wait while we create a database backup...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch('{{ route("admin.settings.backup.create") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    html: `Backup created successfully!<br><small>${data.file_name}</small>`,
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Reload page after 2 seconds to show new backup
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to create backup',
                });
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while creating backup',
            });
        });
    }
    
    // Download backup
    function downloadBackup(filename) {
        window.location.href = `/admin/settings/backup/download/${filename}`;
    }
    
    // Delete backup
    function deleteBackup(filename) {
        Swal.fire({
            title: 'Delete Backup?',
            text: `Are you sure you want to delete "${filename}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/settings/backup/delete/${filename}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        // Reload page after 1.5 seconds
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to delete backup',
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while deleting backup',
                    });
                });
            }
        });
    }
    
    // Test email configuration
    function testEmail() {
        Swal.fire({
            title: 'Test Email',
            text: 'Please wait while we test the email configuration...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // You would implement this endpoint to send a test email
        fetch('/admin/settings/email/test', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Test email sent successfully',
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to send test email',
                });
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while sending test email',
            });
        });
    }
    
    // Form submission handlers
    const forms = {
        'general': { form: 'generalForm', route: '{{ route("admin.settings.general.update") }}', errors: 'generalErrors' },
        'email': { form: 'emailForm', route: '{{ route("admin.settings.email.update") }}', errors: 'emailErrors' },
        'notifications': { form: 'notificationsForm', route: '{{ route("admin.settings.notifications.update") }}', errors: 'notificationsErrors' },
        'security': { form: 'securityForm', route: '{{ route("admin.settings.security.update") }}', errors: 'securityErrors' }
    };
    
    Object.entries(forms).forEach(([tab, config]) => {
        const form = document.getElementById(config.form);
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const errorsDiv = document.getElementById(config.errors);
                
                // Show loading
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                submitBtn.disabled = true;
                
                fetch(config.route, {
                    method: 'PUT',
                    body: JSON.stringify(Object.fromEntries(formData)),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        errorsDiv.classList.add('hidden');
                    } else {
                        errorsDiv.classList.remove('hidden');
                        
                        if (data.errors) {
                            let errorsHtml = '<ul class="list-disc pl-5">';
                            Object.values(data.errors).forEach(error => {
                                errorsHtml += `<li class="text-red-600 dark:text-red-400 text-sm">${error}</li>`;
                            });
                            errorsHtml += '</ul>';
                            errorsDiv.innerHTML = errorsHtml;
                        } else {
                            errorsDiv.innerHTML = `<p class="text-red-600 dark:text-red-400">${data.message}</p>`;
                        }
                        
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while saving',
                    });
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
        }
    });
</script>
@endpush