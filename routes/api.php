<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationPreferenceController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\V1\OffenseController;
use App\Http\Controllers\Api\V1\StandingController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| RESTful API endpoints for external integrations. All routes are prefixed
| with /api and use Sanctum token authentication where required.
|
*/

// Public routes (no authentication required)
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Webhooks
Route::post('/webhooks/sms/delivery-status', [WebhookController::class, 'smsDeliveryStatus'])->name('webhooks.sms');
Route::post('/webhooks/email/events', [WebhookController::class, 'emailEvents'])->name('webhooks.email');

// Protected routes (require Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    // Users (Administrator only)
    Route::middleware('role:administrator')->group(function () {
        Route::apiResource('users', UserController::class)->names([
            'index' => 'api.users.index',
            'store' => 'api.users.store',
            'show' => 'api.users.show',
            'update' => 'api.users.update',
            'destroy' => 'api.users.destroy',
        ]);

        Route::post('/notifications/test', [NotificationController::class, 'test'])->name('api.notifications.test');
    });

    // Students (Staff and Administrator)
    Route::middleware('role:staff|administrator')->group(function () {
        Route::apiResource('students', StudentController::class)->names([
            'index' => 'api.students.index',
            'store' => 'api.students.store',
            'show' => 'api.students.show',
            'update' => 'api.students.update',
            'destroy' => 'api.students.destroy',
        ]);

        Route::apiResource('incidents', IncidentController::class)->names([
            'index' => 'api.incidents.index',
            'store' => 'api.incidents.store',
            'show' => 'api.incidents.show',
            'update' => 'api.incidents.update',
            'destroy' => 'api.incidents.destroy',
        ]);
    });

    // Notifications (All authenticated users)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::get('/notifications/preferences', [NotificationPreferenceController::class, 'show'])->name('api.notifications.preferences.show');
    Route::patch('/notifications/preferences', [NotificationPreferenceController::class, 'update'])->name('api.notifications.preferences.update');

    // API V1 endpoints for SDMS (Compliant with Engineering Specification)
    Route::prefix('v1')->name('api.v1.')->group(function () {
        // Users (Full CRUD with restore/suspend)
        Route::middleware('role:administrator')->group(function () {
            Route::apiResource('users', UserController::class);
            Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
            Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        });

        // Offenses (Filing, Evidence)
        Route::middleware('role:staff|administrator')->group(function () {
            Route::apiResource('offenses', OffenseController::class);
            Route::post('offenses/{offense}/evidence', [OffenseController::class, 'uploadEvidence'])->name('offenses.evidence.upload');
            Route::delete('offenses/{offense}/evidence/{evidence}', [OffenseController::class, 'deleteEvidence'])->name('offenses.evidence.delete');
        });

        // Student Standing & Clearance
        Route::middleware('role:staff|administrator|student')->group(function () {
            Route::get('students/{student}/standing', [StandingController::class, 'show'])->name('students.standing.show');
            Route::get('students/{student}/standing-history', [StandingController::class, 'history'])->name('students.standing.history');
            Route::get('students/{student}/clearance-hold', [StandingController::class, 'clearanceHold'])->name('students.clearance-hold');
        });

        Route::middleware('role:administrator')->group(function () {
            Route::post('students/{student}/clearance-hold/override', [StandingController::class, 'overrideClearanceHold'])->name('students.clearance-hold.override');
        });
    });
});
