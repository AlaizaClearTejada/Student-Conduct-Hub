<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\MfaOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthSecurityFrameworkTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------
    // Username Generation
    // -------------------------------------------------------

    public function test_username_generated_from_first_and_last_name(): void
    {
        $username = User::generateUsername('Juan', 'Salvador');
        $this->assertEquals('jsalvador', $username);
    }

    public function test_username_handles_duplicates(): void
    {
        User::factory()->create(['username' => 'jsalvador']);

        $username = User::generateUsername('Juan', 'Salvador');
        $this->assertEquals('jsalvador1', $username);
    }

    public function test_username_strips_special_characters(): void
    {
        $username = User::generateUsername('María', 'De La Cruz-Santos');
        $this->assertMatchesRegularExpression('/^[a-z0-9]+$/', $username);
    }

    // -------------------------------------------------------
    // Username Login Support
    // -------------------------------------------------------

    public function test_user_can_login_with_username(): void
    {
        $user = User::factory()->create([
            'username' => 'jsalvador',
            'password' => Hash::make('password'),
        ]);

        $component = Volt::test('pages.auth.login')
            ->set('form.login', 'jsalvador')
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasNoErrors();
        $this->assertAuthenticated();
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $component = Volt::test('pages.auth.login')
            ->set('form.login', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasNoErrors();
        $this->assertAuthenticated();
    }

    // -------------------------------------------------------
    // Forced Password Reset on First Login
    // -------------------------------------------------------

    public function test_user_with_must_change_password_is_redirected_to_force_reset(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');
        $response->assertRedirect(route('password.force-reset'));
    }

    public function test_force_reset_page_accessible_when_flag_is_true(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('password.force-reset'));
        $response->assertOk();
    }

    public function test_force_reset_redirects_to_dashboard_when_flag_is_false(): void
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('password.force-reset'));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_force_reset_rejects_weak_password(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('password.force-reset.update'), [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_force_reset_accepts_strong_password_and_clears_flag(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('password.force-reset.update'), [
            'password' => 'S3cure!P@ssw0rd',
            'password_confirmation' => 'S3cure!P@ssw0rd',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('S3cure!P@ssw0rd', $user->fresh()->password));
    }

    public function test_force_reset_creates_audit_log(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user);

        $this->post(route('password.force-reset.update'), [
            'password' => 'S3cure!P@ssw0rd',
            'password_confirmation' => 'S3cure!P@ssw0rd',
        ]);

        $this->assertDatabaseHas('auth_audit_logs', [
            'user_id' => $user->id,
            'event_type' => 'forced_password_reset_completed',
        ]);
    }

    // -------------------------------------------------------
    // Multi-Factor Authentication (MFA)
    // -------------------------------------------------------

    public function test_admin_requires_mfa(): void
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);
        $user->assignRole('administrator');

        $this->assertTrue($user->hasMfaRequired());
    }

    public function test_tribunal_panel_requires_mfa(): void
    {
        $user = User::factory()->create([
            'role_type' => 'tribunal_panel',
            'must_change_password' => false,
        ]);

        $this->assertTrue($user->hasMfaRequired());
    }

    public function test_regular_staff_does_not_require_mfa_by_default(): void
    {
        $user = User::factory()->create([
            'role_type' => 'osdw_staff',
            'mfa_enabled' => false,
            'must_change_password' => false,
        ]);

        $this->assertFalse($user->hasMfaRequired());
    }

    public function test_mfa_otp_generation(): void
    {
        $user = User::factory()->create();

        $otp = $user->generateMfaOtp();

        $this->assertNotNull($otp);
        $this->assertEquals(6, strlen($otp));
        $this->assertNotNull($user->fresh()->mfa_otp);
        $this->assertNotNull($user->fresh()->mfa_otp_expires_at);
        $this->assertEquals(0, $user->fresh()->mfa_otp_attempts);
    }

    public function test_mfa_otp_verification_succeeds_with_correct_code(): void
    {
        $user = User::factory()->create();
        $otp = $user->generateMfaOtp();

        $result = $user->verifyMfaOtp($otp);

        $this->assertTrue($result);
        $this->assertNull($user->fresh()->mfa_otp);
    }

    public function test_mfa_otp_verification_fails_with_wrong_code(): void
    {
        $user = User::factory()->create();
        $user->generateMfaOtp();

        $result = $user->verifyMfaOtp('000000');

        $this->assertFalse($result);
        $this->assertEquals(1, $user->fresh()->mfa_otp_attempts);
    }

    public function test_mfa_otp_locks_out_after_3_attempts(): void
    {
        $user = User::factory()->create();
        $otp = $user->generateMfaOtp();

        $user->verifyMfaOtp('111111');
        $user->refresh();
        $user->verifyMfaOtp('222222');
        $user->refresh();
        $user->verifyMfaOtp('333333');
        $user->refresh();

        // 4th attempt should fail even with the correct OTP
        $result = $user->verifyMfaOtp($otp);
        $this->assertFalse($result);
    }

    public function test_mfa_otp_fails_when_expired(): void
    {
        $user = User::factory()->create();
        $otp = $user->generateMfaOtp();

        // Manually expire the OTP
        $user->update(['mfa_otp_expires_at' => now()->subMinutes(11)]);

        $result = $user->verifyMfaOtp($otp);
        $this->assertFalse($result);
    }

    public function test_mfa_verify_page_sends_otp_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_type' => 'tribunal_panel',
            'must_change_password' => false,
        ]);
        $user->assignRole('administrator');

        $this->actingAs($user);

        $this->get(route('mfa.verify'));

        Notification::assertSentTo($user, MfaOtpNotification::class);
    }

    public function test_mfa_verify_check_logs_audit_on_success(): void
    {
        $user = User::factory()->create([
            'role_type' => 'tribunal_panel',
            'must_change_password' => false,
        ]);
        $user->assignRole('administrator');
        $otp = $user->generateMfaOtp();

        $this->actingAs($user);

        $this->post(route('mfa.verify.check'), ['otp' => $otp]);

        $this->assertDatabaseHas('auth_audit_logs', [
            'user_id' => $user->id,
            'event_type' => 'mfa_success',
        ]);
    }

    public function test_mfa_verified_session_bypasses_middleware(): void
    {
        $user = User::factory()->create([
            'role_type' => 'tribunal_panel',
            'must_change_password' => false,
        ]);
        $user->assignRole('administrator');

        $this->actingAs($user)
            ->withSession(['mfa_verified' => true]);

        $response = $this->get(route('admin.dashboard'));
        $response->assertOk();
    }

    // -------------------------------------------------------
    // Session Timeout
    // -------------------------------------------------------

    public function test_session_lifetime_is_30_minutes(): void
    {
        $this->assertEquals(30, config('session.lifetime'));
    }

    // -------------------------------------------------------
    // Audit Logging
    // -------------------------------------------------------

    public function test_login_failure_creates_audit_log(): void
    {
        $user = User::factory()->create();

        Volt::test('pages.auth.login')
            ->set('form.login', $user->email)
            ->set('form.password', 'wrong-password')
            ->call('login');

        $this->assertDatabaseHas('auth_audit_logs', [
            'email' => $user->email,
            'event_type' => 'login_failed',
        ]);
    }

    public function test_login_success_creates_audit_log(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        Volt::test('pages.auth.login')
            ->set('form.login', $user->email)
            ->set('form.password', 'password')
            ->call('login');

        $this->assertDatabaseHas('auth_audit_logs', [
            'user_id' => $user->id,
            'event_type' => 'login_success',
        ]);
    }

    // -------------------------------------------------------
    // Password Policy Validation
    // -------------------------------------------------------

    public function test_password_policy_rejects_missing_uppercase(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);
        $this->actingAs($user);

        $response = $this->post(route('password.force-reset.update'), [
            'password' => 's3cure!p@ssw0rd',
            'password_confirmation' => 's3cure!p@ssw0rd',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_policy_rejects_missing_number(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);
        $this->actingAs($user);

        $response = $this->post(route('password.force-reset.update'), [
            'password' => 'Secure!Password',
            'password_confirmation' => 'Secure!Password',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_policy_rejects_missing_special_char(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);
        $this->actingAs($user);

        $response = $this->post(route('password.force-reset.update'), [
            'password' => 'S3curePassw0rd',
            'password_confirmation' => 'S3curePassw0rd',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_policy_rejects_under_12_chars(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);
        $this->actingAs($user);

        $response = $this->post(route('password.force-reset.update'), [
            'password' => 'S3c!Pw0r',
            'password_confirmation' => 'S3c!Pw0r',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
