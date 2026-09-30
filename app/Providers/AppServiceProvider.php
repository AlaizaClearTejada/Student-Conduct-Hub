<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (Schema::hasTable('settings')) {
                // General Settings
                config(['app.name' => Setting::get('general', 'app_name', config('app.name'))]);

                $timezone = Setting::get('general', 'timezone', config('app.timezone'));
                config(['app.timezone' => $timezone]);
                date_default_timezone_set($timezone);

                // Email Settings
                config(['mail.from.address' => Setting::get('email', 'from_address', config('mail.from.address'))]);
                config(['mail.from.name' => Setting::get('email', 'from_name', config('mail.from.name'))]);

                // Security Settings
                config(['session.lifetime' => Setting::get('security', 'session_timeout', config('session.lifetime'))]);

                // Global Password Rules
                Password::defaults(function () {
                    $rule = Password::min(Setting::get('security', 'password_min_length', 8));

                    if (Setting::get('security', 'require_uppercase', true)) {
                        $rule->mixedCase();
                    }

                    if (Setting::get('security', 'require_number', true)) {
                        $rule->numbers();
                    }

                    if (Setting::get('security', 'require_special_char', false)) {
                        $rule->symbols();
                    }

                    return $rule;
                });
            }
        } catch (\Throwable $e) {
            // Fails gracefully if database is not migrated yet
        }
    }
}
