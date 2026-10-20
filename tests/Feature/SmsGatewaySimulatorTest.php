<?php

namespace Tests\Feature;

use App\Models\Groupsms;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Services\Sms\BulkSmsSendService;
use App\Services\Sms\SingleSmsSendService;
use App\Services\Sms\SmsGatewaySimulator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * The simulator panel never reaches IranSms / NeginSms. Reuses the bulk-queue fixture (business,
 * advertising sender, balance helpers); inherited tests of that class simply run again here.
 */
class SmsGatewaySimulatorTest extends SmsBulkQueueTest
{
    protected function setUp(): void
    {
        parent::setUp();

        // No injected dispatcher: the real gateway selection runs.
        BulkSmsSendService::setGlobalGatewayDispatcher(null);

        if (!Schema::hasTable('histories')) {
            Schema::create('histories', function (Blueprint $table) {
                $table->increments('id');
                $table->morphs('historiable', 'historiable_index');
                $table->integer('user_id')->unsigned()->nullable();
                $table->integer('callcenter_id')->unsigned()->nullable();
                $table->integer('status')->default(0);
                $table->text('description')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->softDeletes();
            });
        }

        config()->set('services.sms.simulate', false);
        config()->set('services.sms.simulated_panels', ['simulator', 'testsmspanel']);
        Http::fake();
    }

    private function setPanel(string $panel): void
    {
        $this->advSender->update(['sms_panel' => $panel]);
    }

    private function assertSimulatedLogged(): void
    {
        Log::shouldHaveReceived('info')->withArgs(fn ($m) => str_contains((string) $m, 'SMS simulator'))->atLeast()->once();
    }

    public function test_should_simulate_rules(): void
    {
        $this->assertTrue(SmsGatewaySimulator::shouldSimulate('simulator'));
        $this->assertTrue(SmsGatewaySimulator::shouldSimulate('  Simulator '));
        $this->assertTrue(SmsGatewaySimulator::shouldSimulate('TestSmsPanel'));
        $this->assertFalse(SmsGatewaySimulator::shouldSimulate('IranSms'));
        $this->assertFalse(SmsGatewaySimulator::shouldSimulate('NeginSms'));
        $this->assertFalse(SmsGatewaySimulator::shouldSimulate(null));

        config()->set('services.sms.simulate', true);
        $this->assertTrue(SmsGatewaySimulator::shouldSimulate('IranSms'));
        $this->assertTrue(SmsGatewaySimulator::shouldSimulate(null));

        $this->assertStringStartsWith('SIM-', SmsGatewaySimulator::dispatch('simulator', '0912', 'x'));
    }

    /** @dataProvider simulatedPanels */
    public function test_single_send_with_simulated_panel_makes_no_gateway_call(string $panel): void
    {
        Log::spy();
        $this->setPanel($panel);
        $this->seedAdvertisingBalance($this->business->id, 10000);

        $message = app(SingleSmsSendService::class)->send([
            'recipient_phone' => '09351234567',
            'message' => 'تخفیف ویژه',
            'service_type' => 'advertising',
            'sender_number_id' => $this->advSender->id,
            'auto_append_cancel' => false,
        ], $this->business);

        // A real gateway call in this sandbox would throw and mark the message failed.
        $this->assertSame('sent', $message->fresh()->status);
        $this->assertEquals(8800, (float) $this->business->fresh()->advertising_balance);
        $this->assertNotNull(SmsLog::where('sms_message_id', $message->id)->first());
        $this->assertGreaterThan(0, SmsLog::count());
        Http::assertNothingSent();
        $this->assertSimulatedLogged();
    }

    public static function simulatedPanels(): array
    {
        return [['simulator'], ['TestSmsPanel'], [' SIMULATOR ']];
    }

    public function test_global_simulate_flag_overrides_real_panel(): void
    {
        Log::spy();
        config()->set('services.sms.simulate', true);
        $this->seedAdvertisingBalance($this->business->id, 10000);

        $message = app(SingleSmsSendService::class)->send([
            'recipient_phone' => '09351234567',
            'message' => 'تخفیف',
            'service_type' => 'advertising',
            'sender_number_id' => $this->advSender->id,
            'auto_append_cancel' => false,
        ], $this->business);

        $this->assertSame('sent', $message->fresh()->status);
        $this->assertSimulatedLogged();
    }

    public function test_bulk_campaign_with_simulator_panel_sends_and_debits(): void
    {
        Log::spy();
        $this->setPanel('simulator');
        $this->seedAdvertisingBalance($this->business->id, 50000);
        $this->makeRecipients(2);

        $campaign = $this->bulkSendService->createCampaign(
            $this->payload(['scheduled_at' => now()->addDay()->toDateTimeString()]),
            $this->titaniumUser,
            $this->business
        );
        $campaign->update(['scheduled_at' => now()->subMinute()]);
        $this->bulkSendService->executeCampaign($campaign->fresh());

        $campaign->refresh();
        $this->assertSame('completed', $campaign->status);
        $this->assertSame(2, Groupsms::where('request_id', $campaign->id)->where('status', 'sent')->count());
        $this->assertEquals(50000 - 2 * 1200, (float) $this->business->fresh()->advertising_balance);
        $this->assertGreaterThan(0, SmsLog::count());
        Http::assertNothingSent();
        $this->assertSimulatedLogged();
    }

    public function test_non_simulated_panel_still_uses_injected_dispatcher(): void
    {
        $this->setPanel('IranSms');
        $this->seedAdvertisingBalance($this->business->id, 10000);

        $calls = [];
        $service = app(SingleSmsSendService::class)->setGatewayDispatcher(function ($panel) use (&$calls) {
            $calls[] = $panel;
        });
        $service->send([
            'recipient_phone' => '09351234567',
            'message' => 'تخفیف',
            'service_type' => 'advertising',
            'sender_number_id' => $this->advSender->id,
            'auto_append_cancel' => false,
        ], $this->business);
        $this->assertSame(['IranSms'], $calls);

        // The injected dispatcher also wins over the simulator.
        $this->setPanel('simulator');
        $service->send([
            'recipient_phone' => '09351234568',
            'message' => 'تخفیف',
            'service_type' => 'advertising',
            'sender_number_id' => $this->advSender->id,
            'auto_append_cancel' => false,
        ], $this->business);
        $this->assertSame(['IranSms', 'simulator'], $calls);
    }
}
