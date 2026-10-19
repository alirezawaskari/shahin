<?php

namespace Tests\Feature;

use App\Models\SmsAuditLog;
use App\Models\User;
use App\Services\Sms\SmsAuditService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SmsAuditServiceTest extends TestCase
{
    protected SmsAuditService $audit;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');

        // Same shape as database/migrations/2026_10_18_000001_create_sms_audit_logs_and_sender_role.php
        Schema::create('sms_audit_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('sms_message_id')->nullable();
            $t->unsignedBigInteger('sms_campaign_id')->nullable();
            $t->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $t->unsignedBigInteger('sender_id')->nullable();
            $t->string('sender_role', 64)->nullable();
            $t->string('access_tier', 32)->nullable();
            $t->unsignedInteger('request_id')->nullable();
            $t->unsignedInteger('callcenter_id')->nullable();
            $t->string('channel', 32);
            $t->string('service_type', 32)->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
        });

        // User::isSuperAdmin() falls back to a role lookup (hasRole).
        Schema::create('roles', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('display_name')->nullable();
            $t->timestamps();
        });
        Schema::create('role_user', function (Blueprint $t) {
            $t->unsignedInteger('role_id');
            $t->unsignedInteger('user_id');
            $t->string('user_type')->default('App\\Models\\User');
            $t->primary(['user_id', 'role_id', 'user_type']);
        });

        $this->audit = new SmsAuditService();
    }

    public function test_sender_role_falls_back_to_system_without_a_user(): void
    {
        $this->assertSame('system', $this->audit->resolveSenderRole(null));
    }

    public function test_sender_role_uses_level_and_superadmin_label(): void
    {
        $admin = new User(['level' => 'admin']);
        $this->assertSame('admin', $this->audit->resolveSenderRole($admin));

        $super = new User();
        $super->level = 'superAdmin';
        $this->assertSame('superadmin', $this->audit->resolveSenderRole($super));
    }

    public function test_record_writes_one_row_with_sender_business_and_request(): void
    {
        $sender = new User(['level' => 'admin']);
        $sender->id = 7;

        $log = $this->audit->record($sender, SmsAuditService::CHANNEL_BULK, [
            'sms_campaign_id' => 3,
            'business_id' => 5,
            'callcenter_id' => 42,
            'service_type' => 'advertising',
        ]);

        $this->assertInstanceOf(SmsAuditLog::class, $log);
        $this->assertSame(1, SmsAuditLog::count());
        $this->assertSame(7, (int) $log->sender_id);
        $this->assertSame('admin', $log->sender_role);
        $this->assertSame('bulk', $log->channel);
        $this->assertSame(5, (int) $log->ghorfe_online_list_id);
        $this->assertSame(42, (int) $log->callcenter_id);
        $this->assertNull($log->request_id);
        $this->assertNotNull($log->sent_at);
    }

    public function test_record_does_not_throw_when_the_write_fails(): void
    {
        Schema::drop('sms_audit_logs');

        $this->assertNull($this->audit->record(null, SmsAuditService::CHANNEL_OTP, ['business_id' => 1]));
    }
}
