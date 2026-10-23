<?php

namespace Tests\Feature;

use App\Exceptions\ContractMediaException;
use App\Models\Contract;
use App\Models\ContractUsage;
use App\Models\GhorfeOnlineList;
use App\Models\User;
use App\Services\SmsSettings\SmsContractFilesService;
use App\Services\SmsSettings\SmsContractService;
use App\Services\SmsSettings\SmsSignatureRecord;
use App\Services\V1\Admin\ContractMediaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Contract files, locks, client review and signing records.
 *
 * Runs on in-memory SQLite only: the default connection is switched in setUp before any query,
 * so nothing is written to the real database. Files go to Storage::fake('public'), which keeps
 * them under storage/framework/testing, never under storage/app/public.
 */
class SmsContractFilesTest extends TestCase
{
    private ContractMediaService $media;

    private SmsContractFilesService $files;

    private GhorfeOnlineList $business;

    private User $signer;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();

        Storage::fake('public');
        $this->media = app(ContractMediaService::class);
        $this->files = app(SmsContractFilesService::class);

        // A fresh, unsigned business (status off, no signing history line) and its signer.
        $this->business = GhorfeOnlineList::create(['title' => 'کسب‌وکار تست', 'status' => false]);
        $this->signer = User::create(['name' => 'امضا', 'family' => 'کننده', 'phone' => '09121234567', 'level' => 'admin', 'active' => 1]);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('family')->nullable();
            $t->string('phone')->nullable();
            $t->string('password')->nullable();
            $t->string('level')->default('user');
            $t->boolean('active')->default(true);
            $t->string('token')->nullable();
            $t->string('api_token')->nullable();
            $t->string('avatar')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('ghorfe_online_lists', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('en_title')->nullable();
            $t->string('slug')->nullable();
            $t->boolean('status')->default(false);
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('contracts', function (Blueprint $t) {
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
        Schema::create('contract_usages', function (Blueprint $t) {
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
        Schema::create('histories', function (Blueprint $t) {
            $t->increments('id');
            $t->string('historiable_type');
            $t->unsignedBigInteger('historiable_id');
            $t->unsignedInteger('user_id')->nullable();
            $t->unsignedInteger('callcenter_id')->nullable();
            $t->integer('status');
            $t->unsignedInteger('reason_id')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });
        Schema::create('contract_views', function (Blueprint $t) {
            $t->increments('id');
            $t->longText('contract');
            $t->string('contract_type');
            $t->unsignedBigInteger('contract_id');
            $t->timestamps();
        });
        // Spatie media, with the real column set. The id is a char(36) UUID, as the App\Models\Media model expects.
        Schema::create('media', function (Blueprint $t) {
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

    private function contract(): Contract
    {
        return Contract::create(['title' => 'قرارداد تست', 'type' => 1, 'status' => 1, 'content' => 'متن تست']);
    }

    private function pdf(string $name = 'file.pdf', int $kilobytes = 10): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes, 'application/pdf');
    }

    private function assertRefused(string $reason, callable $action): void
    {
        try {
            $action();
            $this->fail("Expected the refusal '{$reason}', but the action went through.");
        } catch (ContractMediaException $e) {
            $this->assertSame($reason, $e->reason);
        }
    }

    public function test_staff_files_are_capped_at_five_and_a_batch_is_all_or_nothing(): void
    {
        $contract = $this->contract();
        $this->media->addStaffFiles($contract, [$this->pdf('1.pdf'), $this->pdf('2.pdf'), $this->pdf('3.pdf'), $this->pdf('4.pdf')]);

        $this->assertRefused('cap', fn () => $this->media->addStaffFiles($contract->fresh(), [$this->pdf('5.pdf'), $this->pdf('6.pdf')]));
        $this->assertCount(4, $contract->fresh()->getMedia('staff_attachments'));

        $this->media->addStaffFiles($contract->fresh(), [$this->pdf('5.pdf')]);
        $this->assertCount(5, $contract->fresh()->getMedia('staff_attachments'));
    }

    public function test_wrong_type_and_oversize_files_are_refused(): void
    {
        $contract = $this->contract();

        $this->assertRefused('mime', fn () => $this->media->addStaffFiles($contract, [UploadedFile::fake()->create('virus.exe', 10)]));
        $this->assertRefused('size', fn () => $this->media->addStaffFiles($contract, [$this->pdf('big.pdf', 10241)]));
        $this->assertCount(0, $contract->fresh()->getMedia('staff_attachments'));
    }

    public function test_general_contract_is_never_locked(): void
    {
        $contract = $this->contract();
        ContractUsage::create([
            'contract_id' => $contract->id,
            'usable_type' => null,
            'usable_id' => null,
            'location' => 'sms_otp',
            'is_active' => 1,
            'position' => 0,
        ]);

        $this->assertFalse($this->media->isStaffLocked($contract->fresh()));
    }

    public function test_contract_assigned_to_a_signed_business_is_locked(): void
    {
        $contract = $this->contract();
        $business = $this->business;
        ContractUsage::create([
            'contract_id' => $contract->id,
            'usable_type' => GhorfeOnlineList::class,
            'usable_id' => $business->id,
            'location' => 'sms_advertising',
            'is_active' => 1,
            'position' => 0,
        ]);

        $this->assertFalse($this->media->isStaffLocked($contract->fresh()));

        SmsSignatureRecord::record($business, $this->signer->id, 'امضای تست', '09120000000', now()->format(SmsSignatureRecord::TIME_FORMAT), 'hash', 'متن امضاشده');

        $this->assertTrue($this->media->isStaffLocked($contract->fresh()));
        $this->assertRefused('locked', fn () => $this->media->addStaffFiles($contract->fresh(), [$this->pdf()]));
    }

    public function test_signing_writes_one_history_line_and_a_snapshot_of_the_signed_text(): void
    {
        $business = $this->business;
        $this->assertNull(SmsSignatureRecord::lineFor($business));

        SmsSignatureRecord::record($business, $this->signer->id, 'علی علوی', '09120000000', '2026-10-23 10:15:00', 'a1b2c3', 'متن امضاشده');

        $line = SmsSignatureRecord::lineFor($business);
        $this->assertNotNull($line);
        $this->assertSame(1, (int) $line->reason_id);
        $this->assertSame(1, (int) $line->status);

        $business = $business->fresh();
        $this->assertTrue($business->hasSignedSmsContract());
        $this->assertTrue((bool) $business->sms_contract_accepted);
        $this->assertSame('علی علوی', $business->sms_contract_signature_name);
        $this->assertSame('2026-10-23 10:15:00', $business->sms_contract_accepted_at->format('Y-m-d H:i:s'));

        $this->assertTrue(DB::table('contract_views')
            ->where('contract_type', GhorfeOnlineList::class)
            ->where('contract_id', $business->id)
            ->where('contract', 'متن امضاشده')
            ->exists());
    }

    public function test_client_files_are_capped_and_a_rejection_needs_a_reason(): void
    {
        $business = $this->business;
        $before = $this->files->clientFiles($business)->count();
        $room = max(0, SmsContractFilesService::CLIENT_LIMIT - $before);

        $this->files->storeClientFiles($business, array_map(fn ($i) => $this->pdf("c{$i}.pdf"), range(1, min(2, $room))));
        $business = $business->fresh();
        $this->assertSame($before + min(2, $room), $this->files->clientFiles($business)->count());

        // Over the cap: refused, nothing added.
        $this->assertRefused('cap', fn () => $this->files->storeClientFiles($business, array_map(fn ($i) => $this->pdf("x{$i}.pdf"), range(1, SmsContractFilesService::CLIENT_LIMIT))));

        $file = $this->files->clientFiles($business)->last();
        $this->assertRefused('reason_required', fn () => $this->files->reject($file, null, '   '));

        $this->files->reject($file, null, 'کیفیت پایین');
        $this->assertSame(SmsContractFilesService::STATUS_REJECTED, $this->files->statusOf($file->fresh()));

        // Rejection never changes the signing record or the gate.
        $this->assertFalse($business->fresh()->hasSmsSignatureRecord());
    }

    public function test_reupload_is_allowed_only_for_rejected_files_and_supersedes_the_old_one(): void
    {
        $business = $this->business;
        $this->files->storeClientFiles($business, [$this->pdf('keep.pdf'), $this->pdf('bad.pdf')]);
        $business = $business->fresh();

        $files = $this->files->clientFiles($business)->values();
        $keep = $files[count($files) - 2];
        $bad = $files[count($files) - 1];
        $this->files->approve($keep, null);
        $this->files->reject($bad, null, 'اسکن ناخوانا');

        $this->assertRefused('not_rejected', fn () => $this->files->reuploadRejected($business, [$keep->id => $this->pdf('new.pdf')]));

        $this->files->reuploadRejected($business, [$bad->id => $this->pdf('new.pdf')]);

        $current = $this->files->clientFiles($business->fresh());
        $this->assertFalse($current->contains(fn ($m) => $m->id === $bad->id), 'the rejected file is superseded');
        $this->assertTrue($current->contains(fn ($m) => $m->id === $keep->id));
        $this->assertCount(count($files), $current);
    }

    public function test_general_and_dedicated_contracts_resolve_separately(): void
    {
        $service = app(SmsContractService::class);
        $business = $this->business;

        $this->assertNull($service->getBusinessOtpContract($business));

        $contract = $this->contract();
        $dedicated = ContractUsage::create([
            'contract_id' => $contract->id,
            'usable_type' => GhorfeOnlineList::class,
            'usable_id' => $business->id,
            'location' => 'sms_otp',
            'is_active' => 1,
            'position' => 0,
        ]);

        $this->assertSame($dedicated->id, $service->getBusinessOtpContract($business)->id);

        $general = ContractUsage::create([
            'contract_id' => $contract->id,
            'usable_type' => null,
            'usable_id' => null,
            'location' => 'sms_otp',
            'is_active' => 1,
            'position' => 0,
        ]);

        $this->assertNull($service->getOtpContract()->usable_id);
        $this->assertSame($general->id, $service->getOtpContract()->id);
        $this->assertSame($dedicated->id, $service->getBusinessOtpContract($business)->id);
    }
}
