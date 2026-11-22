<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        // Explicitly isolate this suite even when shell environment overrides phpunit.xml.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'mail.default' => 'array',
            'cache.default' => 'array', 'session.driver' => 'array']);
        \Illuminate\Support\Facades\DB::purge('sqlite');
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    public function test_registration_dispatches_verification_and_blocks_login(): void
    {
        Notification::fake();
        $data = ['name' => 'مريض اختبار', 'email' => 'verification@example.test',
            'phone' => '+962790000099', 'password' => 'TestSecure!2026', 'password_confirmation' => 'TestSecure!2026'];
        $this->post('/register', $data)->assertRedirect(route('verification.notice'));
        $user = User::where('email', $data['email'])->firstOrFail();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
        $this->post('/login', ['email' => $data['email'], 'password' => $data['password']])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($user)->get('/patient/dashboard')->assertRedirect(route('verification.notice'));
    }

    public function test_real_notification_pipeline_reaches_array_transport_and_signed_link_works(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'patient', 'password' => 'TestSecure!2026']);
        event(new Registered($user));
        $messages = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $html = $messages->first()->getOriginalMessage()->getHtmlBody();
        preg_match('~href="([^"]*/email/verify/[^" ]+)"~', $html, $match);
        $this->assertNotEmpty($match);
        $url = html_entity_decode($match[1]);
        $this->get($url.'&tampered=1')->assertForbidden();
        $expired = URL::temporarySignedRoute('verification.verify', now()->subMinute(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($expired)->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
        $this->get($url)->assertRedirect(route('login'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->post('/login', ['email' => $user->email, 'password' => 'TestSecure!2026'])->assertRedirect(route('patient.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_direct_notification_also_reaches_mail_transport(): void
    {
        $user = User::factory()->unverified()->create();
        $user->sendEmailVerificationNotification();
        $this->assertCount(1, Mail::mailer('array')->getSymfonyTransport()->messages());
    }

    public function test_resend_is_generic_and_limited_per_email_and_ip(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create(['role'=>'patient']);
        for ($i=0; $i<4; $i++) {
            $this->from('/email/verify')->post(route('verification.send'), ['email'=>$user->email])->assertRedirect('/email/verify')->assertSessionHas('status');
        }
        Notification::assertSentToTimes($user, VerifyEmail::class, 3);
        $status = session('status');
        $this->post(route('verification.send'), ['email'=>'unknown@example.test'])->assertSessionHas('status', $status);
        $user->markEmailAsVerified();
        $this->post(route('verification.send'), ['email'=>$user->email])->assertSessionHas('status', $status);
        $this->post(route('verification.send'), ['email'=>$user->email])->assertStatus(429);
        Notification::assertSentToTimes($user, VerifyEmail::class, 3);
    }

    public function test_delivery_failure_keeps_account_and_offers_recovery(): void
    {
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('Synthetic delivery failure'));
        $this->post('/register', ['name'=>'QA', 'email'=>'failed@example.test','phone'=>'+962790000098',
            'password'=>'TestSecure!2026','password_confirmation'=>'TestSecure!2026'])
            ->assertRedirect(route('verification.notice'))->assertSessionHasErrors('email');
        $this->assertDatabaseHas('users',['email'=>'failed@example.test','email_verified_at'=>null]);
    }

    public function test_login_ignores_foreign_role_destination_and_preserves_booking(): void
    {
        $user=User::factory()->create(['role'=>'patient','password'=>'TestSecure!2026']);
        $credentials=['email'=>$user->email,'password'=>'TestSecure!2026'];
        $this->withSession(['url.intended'=>route('admin.dashboard')])->post('/login',$credentials)->assertRedirect(route('patient.dashboard'));
        $this->get('/login')->assertRedirect(route('patient.dashboard'));
        $this->post('/logout');
        $this->withSession(['url.intended'=>route('patient.booking')])->post('/login',$credentials)->assertRedirect(route('patient.booking'));
    }

    public function test_authenticated_unverified_user_can_reach_notice_without_redirect_loop(): void
    {
        $user=User::factory()->unverified()->create(['role'=>'patient']);
        $this->actingAs($user)->get('/login')->assertRedirect(route('verification.notice'));
        $this->get('/email/verify')->assertOk()->assertSee('email/verification-notification');
    }

    public function test_changing_email_requires_verification_of_new_address(): void
    {
        Notification::fake();
        $user=User::factory()->create(['role'=>'patient']);
        $oldUrl=URL::temporarySignedRoute('verification.verify',now()->addHour(),['id'=>$user->id,'hash'=>sha1($user->email)]);
        $this->actingAs($user)->patch(route('patient.profile.update'),[
            'name'=>$user->name,'email'=>'changed@example.test','phone'=>'+962790000097',
        ])->assertRedirect(route('verification.notice'));
        $this->assertNull($user->fresh()->email_verified_at);
        Notification::assertSentToTimes($user,VerifyEmail::class,1);
        $this->get('/patient/dashboard')->assertRedirect(route('verification.notice'));
        $this->get($oldUrl)->assertForbidden();
    }
}
