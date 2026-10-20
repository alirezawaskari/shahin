<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class TargetSmsRoutesTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Use user 63 (the logged in user from session) or first superadmin
        $this->user = User::find(63) ?? User::first();
    }

    public function test_dashboard_sms()
    {
        $response = $this->actingAs($this->user)->get('/dashboard/sms');
        $response->assertStatus(200);
    }

    public function test_settings_contracts()
    {
        $response = $this->actingAs($this->user)->get('/dashboard/sms/settings/contracts');
        $response->assertStatus(200);
    }

    public function test_settings_access_tiers()
    {
        $response = $this->actingAs($this->user)->get('/dashboard/sms/settings/access-tiers');
        $response->assertStatus(200);
    }

    public function test_settings_ai()
    {
        $response = $this->actingAs($this->user)->get('/dashboard/sms/settings/ai');
        $response->assertStatus(200);
    }

    public function test_reports_otp()
    {
        $response = $this->actingAs($this->user)->followingRedirects()->get('/dashboard/sms/reports/otp');
        $response->assertStatus(200);

        $responseWithBusiness = $this->actingAs($this->user)->get('/dashboard/sms/reports/otp?business_id=1');
        $responseWithBusiness->assertStatus(200);
        $responseWithBusiness->assertDontSee('پیامکی منطبق با فیلترهای انتخابی یافت نشد.');
    }

    public function test_reports_advertising()
    {
        $response = $this->actingAs($this->user)->followingRedirects()->get('/dashboard/sms/reports/advertising');
        $response->assertStatus(200);

        $responseWithBusiness = $this->actingAs($this->user)->get('/dashboard/sms/reports/advertising?business_id=1');
        $responseWithBusiness->assertStatus(200);
        $responseWithBusiness->assertDontSee('پیامکی منطبق با فیلترهای انتخابی یافت نشد.');
    }

    public function test_financial_otp()
    {
        $response = $this->actingAs($this->user)->followingRedirects()->get('/dashboard/sms/financial/otp');
        $response->assertStatus(200);

        $responseWithBusiness = $this->actingAs($this->user)->get('/dashboard/sms/financial/otp?business_id=1');
        $responseWithBusiness->assertStatus(200);
    }

    public function test_financial_advertising()
    {
        $response = $this->actingAs($this->user)->followingRedirects()->get('/dashboard/sms/financial/advertising');
        $response->assertStatus(200);

        $responseWithBusiness = $this->actingAs($this->user)->get('/dashboard/sms/financial/advertising?business_id=1');
        $responseWithBusiness->assertStatus(200);
    }

    public function test_reports_cross_business()
    {
        $response = $this->actingAs($this->user)->get('/dashboard/sms/reports/cross-business');
        $response->assertStatus(200);
    }

    public function test_sms_logs()
    {
        $response = $this->actingAs($this->user)->get('/dashboard/sms/logs');
        $response->assertStatus(200);
    }

    public function test_settings_ai_update()
    {
        $response = $this->actingAs($this->user)->post('/dashboard/sms/settings/ai', [
            'provider' => 'gemini',
            'model' => 'google/gemini-2.5-pro-preview',
            'api_key' => 'AIzaSyTestApiKeyForVerification',
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta/openai',
            'temperature' => 0.70,
            'max_tokens' => 2000,
            'role' => 'کپی‌رایتر هوشمند لاله کارت',
            'is_active' => '1',
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
    }

    public function test_settings_access_tier_update()
    {
        $targetUser = User::where('id', '!=', $this->user->id)->first() ?? $this->user;
        $response = $this->actingAs($this->user)->put("/dashboard/sms/settings/access-tiers/{$targetUser->id}", [
            'access_tier' => 'gold',
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertEquals('gold', $targetUser->fresh()->access_tier);
    }

    public function test_reports_otp_export()
    {
        $response = $this->actingAs($this->user)->get('/dashboard/sms/reports/otp/export?business_id=1');
        $response->assertStatus(200);
    }

    public function test_reports_advertising_export()
    {
        $response = $this->actingAs($this->user)->get('/dashboard/sms/reports/advertising/export?business_id=1');
        $response->assertStatus(200);
    }

    public function test_reports_cross_business_export()
    {
        $response = $this->actingAs($this->user)->get('/dashboard/sms/reports/cross-business/export');
        $response->assertStatus(200);
    }

    public function test_financial_check_balance()
    {
        $response = $this->actingAs($this->user)->postJson('/dashboard/sms/financial/check-balance', [
            'business_id' => 1,
            'service_type' => 'otp',
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'status']);
    }

    public function test_setting_virtual_column_query()
    {
        $business = \App\Models\GhorfeOnlineList::first();
        $this->assertNotNull($business);

        // Test the exact query that failed previously
        $senders = $business->senderNumbers()
            ->where('type', 'advertising')
            ->where('status', 'active')
            ->get(['id', 'number']);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $senders);

        // Test pluck
        $numbers = $business->senderNumbers()->pluck('number');
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $numbers);
    }

    public function test_callcenter_routes()
    {
        $routes = [
            '/dashboard/callcenter',
            '/dashboard/callcenter/suspension',
            '/dashboard/callcenter/paid',
            '/dashboard/callcenter/cancellation',
            '/dashboard/callcenter/desktop',
            '/dashboard/callcenter/deleted',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)->get($route);
            $this->assertContains($response->getStatusCode(), [200, 302], "Route {$route} failed with status " . $response->getStatusCode());
            if ($response->getStatusCode() === 500) {
                $response->assertStatus(200); // Will output error details if 500
            }
        }
    }

    public function test_all_sms_pages()
    {
        $routes = [
            '/dashboard/sms',
            '/dashboard/sms/bulk',
            '/dashboard/sms/bulk/queue',
            '/dashboard/sms/bulk/campaigns',
            '/dashboard/sms/single',
            '/dashboard/sms/ai',
            '/dashboard/sms/ai/campaigns',
            '/dashboard/sms/contract/sign',
            '/dashboard/sms/settings',
            '/dashboard/sms/settings/panels',
            '/dashboard/sms/settings/panels/create',
            '/dashboard/sms/settings/senders',
            '/dashboard/sms/settings/senders/create',
            '/dashboard/sms/settings/templates',
            '/dashboard/sms/settings/templates/create',
            '/dashboard/sms/settings/groups',
            '/dashboard/sms/settings/groups/create',
            '/dashboard/sms/settings/contracts',
            '/dashboard/sms/settings/content',
            '/dashboard/sms/settings/access-tiers',
            '/dashboard/sms/settings/auto-responders',
            '/dashboard/sms/settings/auto-responders/create',
            '/dashboard/sms/settings/ai',
            '/dashboard/sms/financial/otp',
            '/dashboard/sms/financial/advertising',
            '/dashboard/sms/reports/otp',
            '/dashboard/sms/reports/advertising',
            '/dashboard/sms/reports/cross-business',
            '/dashboard/sms/logs',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)->followingRedirects()->get($route);
            $this->assertEquals(200, $response->getStatusCode(), "Route {$route} failed with status " . $response->getStatusCode());
        }
    }

    public function test_groupsms_mass_assignment_create()
    {
        $message = \App\Models\Groupsms::create([
            'title' => 'تست پیامک جدید',
            'message' => 'این یک متن تستی برای بررسی mass assignment است.',
            'type' => 'single',
            'status' => 1,
            'phones' => '09123456789',
        ]);
        $this->assertNotNull($message->id);
        $this->assertEquals('تست پیامک جدید', $message->title);
        $message->delete();
    }

    public function test_sms_single_send_endpoint()
    {
        $sender = \App\Models\Setting::where('key', 'sms_sender_number')->first();
        $senderId = $sender ? $sender->id : 1;

        $response = $this->actingAs($this->user)->postJson('/dashboard/sms/single/send', [
            'recipient_phone' => '09121111111',
            'message' => 'متن پیامک تست جهت بررسی ارسال',
            'sender_id' => $senderId,
            'business_id' => 1,
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302, 422]);
    }
}
