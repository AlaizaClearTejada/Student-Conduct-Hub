<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Component;

class SystemSettings extends Component
{
    public string $activeTab = 'general';

    // General Settings
    public string $appName = '';

    public string $timezone = '';

    public string $dateFormat = '';

    public string $academicYear = '';

    // Security Settings
    public int $sessionTimeout = 120;

    public int $maxLoginAttempts = 5;

    public int $lockoutDuration = 15;

    public int $passwordMinLength = 8;

    public bool $requireUppercase = true;

    public bool $requireNumber = true;

    public bool $requireSpecialChar = false;

    // Email Settings
    public string $fromAddress = '';

    public string $fromName = '';

    public bool $sendWelcomeEmail = true;

    public bool $sendIncidentNotifications = true;

    // Notification Settings
    public bool $notifyOnNewIncident = true;

    public bool $notifyOnCaseUpdate = true;

    public bool $notifyAdminOnLoginFailure = true;

    public bool $dailySummaryEmail = false;

    /** @var array<string, string> */
    public array $timezones = [
        'Asia/Manila' => 'Asia/Manila (PHT)',
        'Asia/Singapore' => 'Asia/Singapore (SGT)',
        'Asia/Tokyo' => 'Asia/Tokyo (JST)',
        'UTC' => 'UTC',
        'America/New_York' => 'America/New_York (EST)',
        'America/Los_Angeles' => 'America/Los_Angeles (PST)',
    ];

    /** @var array<string, string> */
    public array $dateFormats = [
        'F j, Y' => 'March 2, 2026',
        'M j, Y' => 'Mar 2, 2026',
        'Y-m-d' => '2026-03-02',
        'd/m/Y' => '02/03/2026',
        'm/d/Y' => '03/02/2026',
    ];

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        // General
        $this->appName = Setting::get('general', 'app_name', 'CSU Student Conduct Management System');
        $this->timezone = Setting::get('general', 'timezone', 'Asia/Manila');
        $this->dateFormat = Setting::get('general', 'date_format', 'F j, Y');
        $this->academicYear = Setting::get('general', 'academic_year', '2025-2026');

        // Security
        $this->sessionTimeout = Setting::get('security', 'session_timeout', 120);
        $this->maxLoginAttempts = Setting::get('security', 'max_login_attempts', 5);
        $this->lockoutDuration = Setting::get('security', 'lockout_duration', 15);
        $this->passwordMinLength = Setting::get('security', 'password_min_length', 8);
        $this->requireUppercase = Setting::get('security', 'require_uppercase', true);
        $this->requireNumber = Setting::get('security', 'require_number', true);
        $this->requireSpecialChar = Setting::get('security', 'require_special_char', false);

        // Email
        $this->fromAddress = Setting::get('email', 'from_address', 'noreply@csu.edu.ph');
        $this->fromName = Setting::get('email', 'from_name', 'CSU Student Conduct Office');
        $this->sendWelcomeEmail = Setting::get('email', 'send_welcome_email', true);
        $this->sendIncidentNotifications = Setting::get('email', 'send_incident_notifications', true);

        // Notifications
        $this->notifyOnNewIncident = Setting::get('notifications', 'notify_on_new_incident', true);
        $this->notifyOnCaseUpdate = Setting::get('notifications', 'notify_on_case_update', true);
        $this->notifyAdminOnLoginFailure = Setting::get('notifications', 'notify_admin_on_login_failure', true);
        $this->dailySummaryEmail = Setting::get('notifications', 'daily_summary_email', false);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveGeneral(): void
    {
        $this->validate([
            'appName' => 'required|string|max:255',
            'timezone' => 'required|string',
            'dateFormat' => 'required|string',
            'academicYear' => 'required|string|max:20',
        ]);

        Setting::set('general', 'app_name', $this->appName);
        Setting::set('general', 'timezone', $this->timezone);
        Setting::set('general', 'date_format', $this->dateFormat);
        Setting::set('general', 'academic_year', $this->academicYear);

        session()->flash('success', 'General settings saved successfully.');
    }

    public function saveSecurity(): void
    {
        $this->validate([
            'sessionTimeout' => 'required|integer|min:5|max:480',
            'maxLoginAttempts' => 'required|integer|min:3|max:10',
            'lockoutDuration' => 'required|integer|min:5|max:60',
            'passwordMinLength' => 'required|integer|min:6|max:32',
        ]);

        Setting::set('security', 'session_timeout', $this->sessionTimeout, 'integer');
        Setting::set('security', 'max_login_attempts', $this->maxLoginAttempts, 'integer');
        Setting::set('security', 'lockout_duration', $this->lockoutDuration, 'integer');
        Setting::set('security', 'password_min_length', $this->passwordMinLength, 'integer');
        Setting::set('security', 'require_uppercase', $this->requireUppercase, 'boolean');
        Setting::set('security', 'require_number', $this->requireNumber, 'boolean');
        Setting::set('security', 'require_special_char', $this->requireSpecialChar, 'boolean');

        session()->flash('success', 'Security settings saved successfully.');
    }

    public function saveEmail(): void
    {
        $this->validate([
            'fromAddress' => 'required|email|max:255',
            'fromName' => 'required|string|max:255',
        ]);

        Setting::set('email', 'from_address', $this->fromAddress);
        Setting::set('email', 'from_name', $this->fromName);
        Setting::set('email', 'send_welcome_email', $this->sendWelcomeEmail, 'boolean');
        Setting::set('email', 'send_incident_notifications', $this->sendIncidentNotifications, 'boolean');

        session()->flash('success', 'Email settings saved successfully.');
    }

    public function saveNotifications(): void
    {
        Setting::set('notifications', 'notify_on_new_incident', $this->notifyOnNewIncident, 'boolean');
        Setting::set('notifications', 'notify_on_case_update', $this->notifyOnCaseUpdate, 'boolean');
        Setting::set('notifications', 'notify_admin_on_login_failure', $this->notifyAdminOnLoginFailure, 'boolean');
        Setting::set('notifications', 'daily_summary_email', $this->dailySummaryEmail, 'boolean');

        session()->flash('success', 'Notification settings saved successfully.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.admin.system-settings')
            ->layout('layouts.admin', ['title' => 'System Configuration'])
            ->title('System Configuration');
    }
}
