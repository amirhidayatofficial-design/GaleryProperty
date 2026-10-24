<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Tests\TestCase;

/**
 * State machine verifikasi akun: registrasi → pending → (verifikasi email) → active; suspended oleh admin.
 */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function pendingUser(array $attributes = []): User
    {
        return User::factory()->unverified()->create(['status' => User::STATUS_PENDING, ...$attributes]);
    }

    private function verificationUrl(User $user, ?string $hash = null, int $minutes = 60): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes($minutes),
            ['id' => $user->id, 'hash' => $hash ?? sha1($user->email)]
        );
    }

    // ---------- Registrasi → pending ----------

    public function test_registration_creates_pending_buyer_and_sends_verification_email(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Fatimah Azzahra',
            'email' => 'fatimah@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'fatimah@example.com')->firstOrFail();
        $this->assertSame(User::STATUS_PENDING, $user->status);
        $this->assertSame('buyer', $user->role);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verification_email_is_in_indonesian(): void
    {
        $user = $this->pendingUser(['name' => 'Fatimah Azzahra']);

        $mail = (new VerifyEmail)->toMail($user);

        $this->assertSame('Verifikasi Email Akun GalleryProperty', $mail->subject);
        $this->assertSame("Assalamu'alaikum, Fatimah!", $mail->greeting);
        $this->assertSame('Verifikasi Alamat Email', $mail->actionText);
    }

    public function test_registration_still_succeeds_when_mail_service_fails(): void
    {
        Notification::shouldReceive('send')->andThrow(new RuntimeException('SMTP down'));

        $this->post('/register', [
            'name' => 'Fatimah',
            'email' => 'fatimah@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', ['email' => 'fatimah@example.com', 'status' => User::STATUS_PENDING]);
    }

    // ---------- Akses akun pending ----------

    public function test_pending_user_is_redirected_to_verification_notice_for_protected_pages(): void
    {
        $user = $this->pendingUser();

        foreach (['/dashboard', '/chat', '/bantuan'] as $url) {
            $this->actingAs($user)->get($url)->assertRedirect(route('verification.notice'));
        }

        $this->actingAs($user)->get('/verify-email')->assertOk()->assertSee($user->email);
        $this->actingAs($user)->get('/profile')->assertOk();
    }

    public function test_pending_user_can_resend_verification_email(): void
    {
        Notification::fake();
        $user = $this->pendingUser();

        $this->actingAs($user)->post(route('verification.send'))
            ->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_resend_shows_error_when_mail_service_fails(): void
    {
        $user = $this->pendingUser();
        Notification::shouldReceive('send')->andThrow(new RuntimeException('SMTP down'));

        $this->actingAs($user)->post(route('verification.send'))->assertSessionHas('error');
    }

    // ---------- pending → active ----------

    public function test_verification_link_activates_account_without_login(): void
    {
        Event::fake([Verified::class]);
        $user = $this->pendingUser();

        $this->get($this->verificationUrl($user))
            ->assertOk()
            ->assertSee('email berhasil diverifikasi')
            ->assertSee('Masuk ke Akun Saya');

        Event::assertDispatched(Verified::class);
        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertSame(User::STATUS_ACTIVE, $user->status);
    }

    public function test_verification_link_redirects_logged_in_user_to_dashboard(): void
    {
        $user = $this->pendingUser();

        $this->actingAs($user)->get($this->verificationUrl($user))
            ->assertRedirect(route('dashboard', absolute: false) . '?verified=1');

        $this->actingAs($user->refresh())->get('/dashboard')->assertOk();
    }

    public function test_invalid_or_expired_link_does_not_activate_account(): void
    {
        $user = $this->pendingUser();

        $this->get($this->verificationUrl($user, sha1('wrong-email')))->assertForbidden();
        $this->get($this->verificationUrl($user, minutes: -1))->assertForbidden();

        $user->refresh();
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertSame(User::STATUS_PENDING, $user->status);
    }

    // ---------- active → pending (ganti email) ----------

    public function test_changing_email_moves_account_back_to_pending_and_sends_new_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', ['name' => $user->name, 'email' => 'baru@example.com'])
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame(User::STATUS_PENDING, $user->status);
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('verification.notice'));
    }

    // ---------- suspended ----------

    public function test_suspended_user_cannot_login(): void
    {
        $user = User::factory()->create(['status' => User::STATUS_SUSPENDED]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Akun Anda sedang ditangguhkan oleh admin. Silakan hubungi admin GalleryProperty untuk informasi lebih lanjut.']);

        $this->assertGuest();
    }

    public function test_user_suspended_during_session_is_logged_out(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk();

        $user->update(['status' => User::STATUS_SUSPENDED]);

        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_verifying_email_does_not_lift_suspension(): void
    {
        $user = $this->pendingUser(['status' => User::STATUS_SUSPENDED]);

        $this->get($this->verificationUrl($user))->assertOk()->assertSee('ditangguhkan');

        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertSame(User::STATUS_SUSPENDED, $user->status);
    }

    // ---------- Transisi oleh admin ----------

    public function test_admin_creating_pending_user_sends_verification_email(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Agency Baru',
            'email' => 'agency.baru@example.com',
            'role' => 'agency_partner',
            'status' => User::STATUS_PENDING,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHas('success');

        $user = User::where('email', 'agency.baru@example.com')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_admin_activating_pending_user_verifies_email_manually(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $user = $this->pendingUser();

        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => User::STATUS_ACTIVE,
        ])->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertSame(User::STATUS_ACTIVE, $user->status);
        $this->assertTrue($user->hasVerifiedEmail());
        Notification::assertNothingSent();
    }

    public function test_admin_setting_active_user_to_pending_requires_reverification(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => User::STATUS_PENDING,
        ]);

        $user->refresh();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
