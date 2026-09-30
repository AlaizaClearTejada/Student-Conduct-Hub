# Authentication & Access Control Framework Specification & Implementation Guide

This document provides a comprehensive overview of the newly implemented **Enterprise Authentication & Access Control Framework** in the Student Conduct Management System (SCMS), built in compliance with the CSU Lens security model.

---

## 1. Executive Summary & Core Objectives

The Authentication Framework implements multi-layered security controls to protect user credentials, prevent unauthorized access, enforce multi-factor verification for administrative roles, and maintain a complete audit trail for compliance.

### Key Capabilities

* 👤 **Dual-Credential Login:** Supports both email addresses and alphanumeric usernames (`jsalvador`).
* 🔒 **Forced Password Reset on First Login:** Blocks dashboard access for temporary/provisioned accounts until a strong password is created.
* 🛡️ **Multi-Factor Authentication (MFA):** Email OTP verification for privileged roles (**Administrator**, **Tribunal Panel**, **System Auditor**).
* ⏳ **Session & Rate Control:** 30-minute inactivity session timeout and 5-attempt rate limiting per minute per IP address.
* 📊 **Security Audit Trail:** Complete logging of authentication events (`login_success`, `login_failed`, `lockout`, `mfa_success`, `mfa_failed`, `forced_password_reset_completed`, `logout`).

---

## 2. Component Architecture & Workflow

```
                        [User Login Request]
                                 │ (Email or Username)
                                 ▼
                     [LoginForm / Livewire Component]
                                 │
                   ┌─────────────┴─────────────┐
                   ▼                           ▼
        [Rate Limiter Check]        [Credentials Validation]
         (Max 5 attempts/min)          (Bcrypt Cost Factor 12)
                   │                           │
                   └─────────────┬─────────────┘
                                 ▼
                    [Authentication Success]
                                 │
                 ┌───────────────┴───────────────┐
                 ▼                               ▼
    [Check: must_change_password]       [Check: hasMfaRequired]
     (Redirect: /password/force-reset)   (Redirect: /mfa/verify)
                 │                               │
                 └───────────────┬───────────────┘
                                 ▼
                      [Access Granted to Dashboard]
```

---

## 3. Detailed Feature Specifications

### 3.1 User Credential Management

| Requirement | Specification & Implementation |
| :--- | :--- |
| **Username Format** | Alphanumeric `[First Initial][Last Name]` (e.g. `jsalvador`). Auto-generated via [`User::generateUsername()`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Models/User.php#L62) with duplicate index handling. |
| **Dual Credential Authentication** | [`LoginForm.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Livewire/Forms/LoginForm.php) accepts either an email address or username, auto-detecting the field format via `FILTER_VALIDATE_EMAIL`. |
| **Forced Password Reset** | [`EnsurePasswordResetOnFirstLogin.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Http/Middleware/EnsurePasswordResetOnFirstLogin.php) middleware intercepts requests when `must_change_password = true` and redirects to [`/password/force-reset`](file:///c:/MyXampp/htdocs/student-conduct-management-system/resources/views/auth/force-reset.blade.php). |
| **Password Policy** | [`ForceResetPasswordController.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Http/Controllers/Auth/ForceResetPasswordController.php) requires: min 12 characters, uppercase letter, lowercase letter, number, and special character. |

### 3.2 Multi-Factor Authentication (MFA)

| Requirement | Specification & Implementation |
| :--- | :--- |
| **Mandatory MFA Roles** | **Administrator**, **Tribunal Panel** (`role_type: tribunal_panel`), and **System Auditor** (`role_type: system_auditor`). |
| **OTP Delivery Engine** | [`MfaOtpNotification.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Notifications/MfaOtpNotification.php) delivers a 6-digit random code to the user's registered email address. |
| **Code Expiration & Lockout** | OTP codes expire after **10 minutes** (`mfa_otp_expires_at`). Maximum of **3 retry attempts** per OTP before locking out current code ([`MfaController.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Http/Controllers/Auth/MfaController.php)). |
| **MFA Verification UI** | Interactive 6-digit split input box with real-time countdown timer and resend support ([`mfa-verify.blade.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/resources/views/auth/mfa-verify.blade.php)). |

### 3.3 Session Management & Transport Hardening

| Requirement | Specification & Implementation |
| :--- | :--- |
| **Session Lifetime** | Inactivity timeout set to **30 minutes** in [`config/session.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/config/session.php). |
| **Rate Limiting** | Max 5 failed login attempts per minute per IP address (`throttleKey()`). |
| **Audit Logs** | All authentication actions append structured records to `auth_audit_logs` storing `user_id`, `email`, `ip_address`, `user_agent`, `event_type`, and ISO-8601 timestamps. |

---

## 4. File Inventory

### Database Migrations
* [`database/migrations/2026_08_02_000000_add_auth_security_fields_to_users_table.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/database/migrations/2026_08_02_000000_add_auth_security_fields_to_users_table.php): Adds `username`, `must_change_password`, `mfa_enabled`, `mfa_otp`, `mfa_otp_expires_at`, and `mfa_otp_attempts`.

### Models & Middleware
* [`app/Models/User.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Models/User.php): Core model with helper methods `generateUsername()`, `generateMfaOtp()`, `verifyMfaOtp()`, and `hasMfaRequired()`.
* [`app/Http/Middleware/EnsurePasswordResetOnFirstLogin.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Http/Middleware/EnsurePasswordResetOnFirstLogin.php): Intercepts forced password reset accounts.
* [`app/Http/Middleware/EnsureMfaVerified.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Http/Middleware/EnsureMfaVerified.php): Intercepts privileged role logins for MFA OTP validation.

### Controllers & Forms
* [`app/Livewire/Forms/LoginForm.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Livewire/Forms/LoginForm.php): Supports username/email dual authentication and rate limiting.
* [`app/Http/Controllers/Auth/ForceResetPasswordController.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Http/Controllers/Auth/ForceResetPasswordController.php): Processes forced password changes with 12-char policy rules.
* [`app/Http/Controllers/Auth/MfaController.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Http/Controllers/Auth/MfaController.php): Manages OTP display, verification attempts, and email resends.

### User Interface Blade Views
* [`resources/views/auth/force-reset.blade.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/resources/views/auth/force-reset.blade.php): Password reset view with reactive rule checklist.
* [`resources/views/auth/mfa-verify.blade.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/resources/views/auth/mfa-verify.blade.php): 6-digit OTP verification view.
* [`resources/views/livewire/pages/auth/login.blade.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/resources/views/livewire/pages/auth/login.blade.php): Updated login template supporting Email or Username.

---

## 5. Verification & Test Suite

The framework includes a comprehensive test suite covering all functional and security requirements.

### Test Execution Command

```bash
php artisan test --compact tests/Feature/Auth/AuthSecurityFrameworkTest.php
```

### Verified Test Results

```
  .............................

  Tests:    29 passed (48 assertions)
  Duration: 12.04s
```
