<?php

use App\Http\Controllers\StudentChatController;
use App\Http\Controllers\StudentConductController;
use App\Imports\StudentImport;
use App\Models\CaseEvidence;
use App\Models\IncidentReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Language switcher
Route::get('language/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'fil'], strict: true)) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('language.switch');

Route::view('/', 'welcome')
    ->middleware('guest')
    ->name('welcome');

Route::view('/landing', 'landing')
    ->name('landing');

// Role-based dashboard redirect
Route::get('dashboard', function () {
    $user = Auth::user();

    if ($user->hasRole('administrator')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('staff')) {
        return redirect()->route('staff.dashboard');
    }

    if ($user->hasRole('student')) {
        return redirect()->route('student.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Student Portal Routes - Protected with Auth and Role Middleware
// CRITICAL: Students should NEVER access /admin/* routes
// These routes are STUDENT-ONLY. Staff have separate admin routes.
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentConductController::class, 'index'])->name('dashboard');
    Route::get('/records', [StudentConductController::class, 'records'])->name('records');
    Route::get('/records/{record}', [StudentConductController::class, 'show'])->name('records.show');
    Route::post('/records/{record}/answer', [StudentConductController::class, 'submitAnswer'])->name('records.submit-answer');
    Route::get('/offense-rules', [StudentConductController::class, 'offenseRules'])->name('offense-rules');
    Route::get('/profile', [StudentConductController::class, 'profile'])->name('profile');
    Route::post('/chat', [StudentChatController::class, 'chat'])->name('chat');
});

// Staff Portal Routes - Incident Reporting System
// Staff members can submit incident reports but cannot access student records
Route::middleware(['auth', 'role:staff|administrator'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\StaffIncidentController::class, 'index'])->name('dashboard');

    // Reporting Routes
    Route::get('/formal-charge', fn () => view('staff.formal-charge'))->name('formal-charge');
    Route::get('/report/create', [\App\Http\Controllers\StaffIncidentController::class, 'create'])->name('report.create');
    Route::post('/report', [\App\Http\Controllers\StaffIncidentController::class, 'store'])->name('report.store');

    // Student Registration Management (Page-based with breadcrumbs)
    Route::get('/students', fn () => view('staff.students.index'))->name('students');
    Route::get('/students/create', fn () => view('staff.students.create'))->name('students.create');
    Route::get('/students/import-template', function () {
        return response(StudentImport::templateCsv(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sia-masterlist-template.csv"',
        ]);
    })->name('students.import-template');
    Route::get('/students/{student}', fn ($student) => view('staff.students.show', ['studentId' => $student]))->name('students.show');
    Route::get('/students/{student}/edit', fn ($student) => view('staff.students.edit', ['studentId' => $student]))->name('students.edit');

    // View their own submissions only
    Route::get('/report/{report}', [\App\Http\Controllers\StaffIncidentController::class, 'show'])->name('report.show')
        ->middleware('can:view,report'); // Enforces IncidentReportPolicy

    Route::get('/my-reports', [\App\Http\Controllers\StaffIncidentController::class, 'myReports'])->name('my-reports');
});

// Admin Portal Routes
Route::middleware(['auth', 'role:administrator'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
    Route::get('/users', \App\Livewire\Admin\UserManagement::class)->name('users');
    Route::get('/cases', \App\Livewire\Admin\CaseManagement::class)->name('cases');
    Route::get('/audit-logs', \App\Livewire\Admin\AuditLogs::class)->name('audit-logs');
    Route::get('/settings', \App\Livewire\Admin\SystemSettings::class)->name('settings');

    // Evidence file downloads
    Route::get('/evidence/{evidence}', function (CaseEvidence $evidence) {
        if (! Storage::disk('local')->exists($evidence->file_path)) {
            abort(404, 'Evidence file not found.');
        }

        return Storage::disk('local')->download($evidence->file_path, $evidence->file_name);
    })->name('evidence.download');

    Route::get('/report-evidence/{report}', function (IncidentReport $report) {
        if (! $report->evidence_path || ! Storage::disk('local')->exists($report->evidence_path)) {
            abort(404, 'Evidence file not found.');
        }

        return Storage::disk('local')->download($report->evidence_path, basename($report->evidence_path));
    })->name('report-evidence.download');
});

// Tribunal Module Routes - Strictly isolated
Route::middleware(['auth', 'tribunal'])->prefix('dashboard/tribunal')->name('tribunal.')->group(function () {
    Route::get('/cases', [\App\Http\Controllers\TribunalController::class, 'index'])->name('cases.index');
    Route::post('/documents', [\App\Http\Controllers\TribunalDocumentController::class, 'upload'])->name('documents.upload');
    Route::get('/cases/{case}', [\App\Http\Controllers\TribunalController::class, 'show'])->name('cases.show');
    Route::get('/cases/{case}/document', [\App\Http\Controllers\TribunalController::class, 'document'])->name('cases.document');
    Route::patch('/cases/{case}/status', [\App\Http\Controllers\TribunalController::class, 'updateStatus'])->name('cases.updateStatus');
});

require __DIR__.'/auth.php';
