<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Concerns\BuildsSmsFixture;
use Tests\TestCase;

/**
 * Smoke test of the SMS routes as a super admin. Runs on the in-memory SQLite fixture only
 * (BuildsSmsFixture), never on the real database: the POST and PUT routes below write rows.
 */
class TargetSmsRoutesTest extends TestCase
{
    use BuildsSmsFixture;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildSmsFixture();
        $this->createPagesTables();
        // The first business is id 1 in the fixture, so the business_id=1 links below point at it.
        $this->user = $this->superAdminUser;

        // One sent OTP and one sent advertising message for that business, so the reports have rows to show.
        foreach (['otp' => 'کد تایید شما 1234', 'advertising' => 'پیشنهاد ویژه امروز'] as $serviceType => $text) {
            \App\Models\Groupsms::create([
                'ghorfe_online_list_id' => $this->businessA->id,
                'direction' => 'outbound',
                'service_type' => $serviceType,
                'recipient_phone' => '09121110001',
                'phones' => '09121110001',
                'content' => $text,
                'message' => $text,
                'status' => 'sent',
                'classification' => 'regular',
                'user_id' => $this->adminUser->id,
                'created_by' => $this->adminUser->id,
            ]);
        }
    }

    /** Tables the SMS pages read beyond the fixture: AI settings, call centers, contracts and their usages. */
    private function createPagesTables(): void
    {
        $schema = \Illuminate\Support\Facades\Schema::class;

        if (! $schema::hasTable('ai_settings')) {
            $schema::create('ai_settings', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->id();
                $t->string('provider')->nullable();
                $t->string('model')->nullable();
                $t->text('api_key')->nullable();
                $t->string('base_url')->nullable();
                $t->float('temperature')->nullable();
                $t->unsignedInteger('max_tokens')->nullable();
                $t->string('role')->nullable();
                $t->boolean('is_active')->default(false);
                $t->timestamps();
            });
        }

        if (! $schema::hasTable('callcenters')) {
            $schema::create('callcenters', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->id();
                $t->unsignedInteger('user_id')->nullable();
                $t->unsignedInteger('request_id')->nullable();
                $t->string('status')->nullable();
                $t->softDeletes();
                $t->timestamps();
            });
        }

        if (! $schema::hasTable('media')) {
            // Spatie media with the real column set (the id is the UUID the App\Models\Media model expects).
            $schema::create('media', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->char('id', 36)->primary();
                $t->morphs('model');
                $t->char('uuid', 36)->nullable()->unique();
                $t->string('collection_name');
                $t->string('name');
                $t->string('file_name');
                $t->string('mime_type')->nullable();
                $t->string('disk');
                $t->string('conversions_disk')->nullable();
                $t->unsignedBigInteger('size');
                $t->json('manipulations');
                $t->json('custom_properties');
                $t->json('generated_conversions');
                $t->json('responsive_images');
                $t->unsignedInteger('order_column')->nullable()->index();
                $t->nullableTimestamps();
            });
        }

        if (! $schema::hasTable('ghorfe_online_list_user')) {
            $schema::create('ghorfe_online_list_user', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->unsignedBigInteger('ghorfe_online_list_id');
                $t->unsignedInteger('user_id');
                $t->primary(['ghorfe_online_list_id', 'user_id']);
            });
        }

        if (! $schema::hasTable('representations')) {
            $schema::create('representations', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->id();
                $t->string('name')->nullable();
                $t->integer('status')->default(1);
                $t->softDeletes();
                $t->timestamps();
            });
        }

        if (! $schema::hasTable('centers')) {
            $schema::create('centers', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->id();
                $t->string('name')->nullable();
                $t->integer('status')->default(1);
                $t->softDeletes();
                $t->timestamps();
            });
        }

        if (! $schema::hasTable('areas')) {
            $schema::create('areas', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->id();
                $t->string('name')->nullable();
                $t->unsignedInteger('city_id')->nullable();
                $t->timestamps();
            });
        }

        if (! $schema::hasTable('reasons')) {
            $schema::create('reasons', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->id();
                $t->string('title')->nullable();
                $t->string('name')->nullable();
                $t->integer('status')->default(1);
                $t->softDeletes();
                $t->timestamps();
            });
        }

        if (! $schema::hasTable('categories')) {
            $schema::create('categories', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('parent_id')->nullable();
                $t->string('name')->nullable();
                $t->string('title')->nullable();
                $t->boolean('isActive')->default(true);
                $t->unsignedInteger('position')->default(0);
                $t->softDeletes();
                $t->timestamps();
            });
        }

        if (! $schema::hasTable('contract_folders')) {
            $schema::create('contract_folders', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->id();
                $t->string('title')->nullable();
                $t->string('name')->nullable();
                $t->boolean('is_active')->default(true);
                $t->unsignedInteger('position')->default(0);
                $t->softDeletes();
                $t->timestamps();
            });
        }

        if (! $schema::hasTable('services')) {
            $schema::create('services', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->id();
                $t->string('title')->nullable();
                $t->string('name')->nullable();
                $t->integer('status')->default(1);
                $t->softDeletes();
                $t->timestamps();
            });
        }

        if (! $schema::hasTable('contracts')) {
            $schema::create('contracts', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('contract_folder_id')->nullable();
                $t->unsignedTinyInteger('type')->nullable();
                $t->string('title')->nullable();
                $t->longText('content')->nullable();
                $t->string('file')->nullable();
                $t->integer('status')->default(1);
                $t->softDeletes();
                $t->timestamps();
            });
        }

        if (! $schema::hasTable('contract_usages')) {
            $schema::create('contract_usages', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('contract_id');
                $t->string('usable_type')->nullable();
                $t->unsignedBigInteger('usable_id')->nullable();
                $t->string('location');
                $t->string('contract_number')->nullable();
                $t->date('contract_date')->nullable();
                $t->boolean('is_active')->default(true);
                $t->unsignedInteger('position')->default(0);
                $t->softDeletes();
                $t->timestamps();
            });
        }

        // Filter-modal lists the callcenter pages load (FilterServiceProvider::tasks_requires), columns as in
        // database/migrations (2022_07_09 organizations, 2018_12_31 provinces, 2022_07_06 recruitment_notices).
        if (! $schema::hasTable('organizations')) {
            $schema::create('organizations', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->unsignedInteger('id')->primary();
                $t->unsignedInteger('parent_id')->nullable();
                $t->string('archive_number', 110)->nullable();
                $t->string('organ_code', 110)->nullable();
                $t->integer('precent')->nullable();
                $t->string('fa_name')->nullable();
                $t->string('en_name')->nullable();
                $t->bigInteger('credit')->nullable();
                $t->string('agent_organ')->nullable();
                $t->string('slug')->nullable();
                $t->text('header_description')->nullable();
                $t->integer('status')->default(1);
                $t->integer('archive_status')->default(1);
                $t->string('type')->default('contracting_party');
                $t->text('image')->nullable();
                $t->text('document')->nullable();
                $t->string('sms_code')->nullable();
                $t->string('call')->nullable();
                $t->text('address')->nullable();
                $t->text('stamp')->nullable();
                $t->softDeletes();
                $t->timestamps();
            });
        }

        if (! $schema::hasTable('organization_service')) {
            $schema::create('organization_service', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->unsignedInteger('service_id');
                $t->unsignedInteger('organization_id');
                $t->primary(['service_id', 'organization_id']);
            });
        }

        if (! $schema::hasTable('provinces')) {
            $schema::create('provinces', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->increments('id');
                $t->string('name');
                $t->integer('code');
                $t->softDeletes();
                $t->timestamps();
            });
        }

        if (! $schema::hasTable('recruitment_notices')) {
            $schema::create('recruitment_notices', function (\Illuminate\Database\Schema\Blueprint $t) {
                $t->integer('id')->primary();
                $t->string('title', 100);
                $t->text('description');
                $t->integer('status')->default(1);
                $t->text('image')->nullable();
                $t->unsignedInteger('province_id')->nullable();
                $t->unsignedInteger('representation_id');
                // The model uses SoftDeletes; the migration has no deleted_at, so the fixture follows the model.
                $t->softDeletes();
                $t->timestamps();
            });
        }
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
