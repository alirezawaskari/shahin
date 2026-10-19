<?php

namespace App\Console\Commands;

use App\Models\AdvertisingBalance;
use App\Models\CategoryPermission;
use App\Models\Employee;
use App\Models\GhorfeOnlineList;
use App\Models\OtpBalance;
use App\Models\Role;
use App\Models\SenderNumber;
use App\Models\ShahinPackage;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Creates 4 fixed test users exercising the access boundaries the SMS module
 * branch (task.md) built: superAdmin CMS access, a business fully onboarded
 * onto SMS (package + contract signed + balances), a business NOT entitled
 * to the SMS module at all (package without the "پیامک" category), and a
 * staff member of an entitled business who still lacks the confidential-SMS
 * permission.
 *
 * Idempotent: every record is looked up by a stable natural key (phone for
 * users, en_title for businesses, number for sender numbers, slug for the
 * package) and updated in place rather than re-inserted, so running this
 * command twice is safe.
 *
 * Usage: php artisan sms:create-test-users
 */
class CreateSmsTestUsers extends Command
{
    protected $signature = 'sms:create-test-users';

    protected $description = 'Create 4 fixed test users covering the SMS module access boundaries (superAdmin, two businesses, one staff member) for manual QA.';

    private const PASSWORD = 'password123';

    public function handle(): int
    {
        DB::transaction(function () {
            $superAdmin = $this->makeSuperAdmin();

            [$businessA, $ownerA] = $this->makeBusinessA();
            [$businessB, $ownerB] = $this->makeBusinessB();
            $staffA = $this->makeBusinessAStaff($businessA);

            $this->printSummary([
                [
                    'email' => $superAdmin->email,
                    'role' => 'superAdmin',
                    'business' => '-',
                    'note' => 'Full system access; can edit SMS contract/regulatory text and categories in SmsCmsSettingsController (تنظیمات پیامک).',
                ],
                [
                    'email' => $ownerA->email,
                    'role' => 'ghorfeAdmin (owner)',
                    'business' => $businessA->title,
                    'note' => 'Business A: subscribed to a package WITH the "پیامک" category, contract already signed, has a sender number with balance. Goes straight into send/dashboard/reports. Also directly granted view-confidential-sms, so مالی/حقوقی message text is visible to this user.',
                ],
                [
                    'email' => $ownerB->email,
                    'role' => 'ghorfeAdmin (owner)',
                    'business' => $businessB->title,
                    'note' => 'Business B: subscribed to a package WITHOUT the "پیامک" category attached. Used to confirm the SMS menu/module stays invisible (package-gating fix).',
                ],
                [
                    'email' => $staffA->email,
                    'role' => 'ghorfeAdmin (staff)',
                    'business' => $businessA->title,
                    'note' => 'Same business as the Business A owner (shared data), but WITHOUT view-confidential-sms. مالی/حقوقی message content must appear redacted for this user, unlike the owner above.',
                ],
            ]);
        });

        return self::SUCCESS;
    }

    /**
     * 1) superAdmin — level alone is enough for isSuperAdmin()/SmsCmsSettingsController's
     * abort_unless() gate, no business needed. Still given a bare employee
     * row (no business attached) because several pre-existing, unrelated
     * admin views (e.g. Factor::scope..., the main dashboard) read
     * auth()->user()->employee->id unconditionally — out of scope to patch
     * those views themselves, but a superAdmin test account needs to
     * survive hitting them.
     */
    private function makeSuperAdmin(): User
    {
        $user = $this->upsertUser(
            phone: '09000000001',
            email: 'sms-test-superadmin@test.local',
            name: 'ادمین',
            family: 'تست کل',
            level: 'superAdmin',
            accessTier: 'titanium'
        );

        $this->upsertEmployeeRow($user, [
            'ghorfe_online_list_id' => null,
            'status' => 1,
            'business_phone' => '09000000001',
            'show_status' => 1,
        ]);

        return $user;
    }

    /**
     * 2) Business A owner — fully onboarded: package WITH "پیامک" attached,
     * contract already accepted, a sender number + non-zero advertising
     * balance, and a non-zero OTP balance. Also given view-confidential-sms
     * directly so مالی/حقوقی content is visible (the positive case staff
     * member #4 contrasts against).
     *
     * @return array{0: GhorfeOnlineList, 1: User}
     */
    private function makeBusinessA(): array
    {
        $owner = $this->upsertUser(
            phone: '09000000002',
            email: 'sms-test-bizA-owner@test.local',
            name: 'مالک',
            family: 'کسب‌وکار الف',
            level: 'ghorfeAdmin',
            accessTier: 'diamond'
        );

        // Reuse a real, already-active package: 2026_10_11_000004_add_sms_dashboard_menu_and_package_attachment
        // attached the "پیامک" category to every shahin_package that already existed when it
        // ran, so any currently-active package already grants SMS. Falls back to creating one
        // if the local DB genuinely has none (fresh seed).
        $package = ShahinPackage::where('is_active', true)->orderBy('id')->first()
            ?? $this->makePackageWithSms();
        $this->ensurePackageHasSmsCategory($package);

        $business = $this->upsertBusiness(
            enTitle: 'sms-test-business-a',
            title: 'کسب‌وکار تستی الف (پیامک فعال)',
            ownerId: $owner->id,
            shahinPackageId: $package->id,
            smsContractAccepted: true
        );

        $this->attachEmployee($owner, $business, '02100000002');

        $senderNumber = $this->upsertAdvertisingSenderNumber('+989000000002', 'simulator');
        $business->senderNumbers()->syncWithoutDetaching([
            $senderNumber->id => ['assigned_at' => now()],
        ]);

        $this->ensureAdvertisingBalance($business, 500000.00);
        $this->ensureOtpBalance($business, 200000.00);

        if (! $owner->hasPermission('view-confidential-sms')) {
            $owner->givePermission('view-confidential-sms');
        }

        $this->seedSampleMessages($business, $senderNumber);

        return [$business, $owner];
    }

    /**
     * 3) Business B owner — subscribed to a package that deliberately has
     * no "پیامک" category attached, so the SMS menu/module must stay
     * invisible for this business (the bug 2026_10_11_000004 fixed, kept
     * reproducible here via a package that migration never touches again).
     *
     * @return array{0: GhorfeOnlineList, 1: User}
     */
    private function makeBusinessB(): array
    {
        $owner = $this->upsertUser(
            phone: '09000000003',
            email: 'sms-test-bizB-owner@test.local',
            name: 'مالک',
            family: 'کسب‌وکار ب',
            level: 'ghorfeAdmin'
        );

        $package = $this->makePackageWithoutSms();

        $business = $this->upsertBusiness(
            enTitle: 'sms-test-business-b',
            title: 'کسب‌وکار تستی ب (پیامک غیرفعال)',
            ownerId: $owner->id,
            shahinPackageId: $package->id,
            smsContractAccepted: false
        );

        $this->attachEmployee($owner, $business, '02100000003');

        return [$business, $owner];
    }

    /**
     * 4) Business A staff — an Employee row pointed at the SAME business as
     * the Business A owner (so they share data), but this user is never
     * granted view-confidential-sms, unlike the owner. Used to prove
     * SmsConfidentialityGuard redacts مالی/حقوقی content for this user.
     */
    private function makeBusinessAStaff(GhorfeOnlineList $businessA): User
    {
        $staff = $this->upsertUser(
            phone: '09000000004',
            email: 'sms-test-bizA-staff@test.local',
            name: 'کارمند',
            family: 'کسب‌وکار الف',
            level: 'ghorfeAdmin'
        );

        $this->attachEmployee($staff, $businessA, '02100000004');

        // Deliberately NOT given view-confidential-sms — that is the whole point of this user.
        if ($staff->hasPermission('view-confidential-sms')) {
            $staff->removePermission('view-confidential-sms');
        }

        return $staff;
    }

    /**
     * Create or update a test user by phone (the only DB-unique, NOT NULL
     * identifier on `users`) and reset its password every run so the
     * printed credentials always match what is actually in the database.
     */
    private function upsertUser(string $phone, string $email, string $name, string $family, string $level, string $accessTier = 'silver'): User
    {
        $user = User::updateOrCreate(
            ['phone' => $phone],
            [
                'email' => $email,
                'name' => $name,
                'family' => $family,
                'level' => $level,
                'access_tier' => $accessTier,
                'active' => true,
                'confirm' => true,
                'sex' => 0,
            ]
        );

        $user->update(['password' => Hash::make(self::PASSWORD)]);

        $this->attachRole($user, $level);

        return $user;
    }

    /**
     * This local dev database has zero rows in `roles`/`role_user` for ANY
     * user, not just these test ones — every admin view that reads
     * auth()->user()->roles->first() (e.g. resources/views/Admin/index.blade.php)
     * crashes on a null first() regardless of who logs in. That's a
     * pre-existing environment/data gap, out of scope to patch in main's
     * views/models — but giving OUR OWN test users a real Laratrust role is
     * squarely in scope, since it's this command's job to make them usable.
     */
    private function attachRole(User $user, string $level): void
    {
        $roleName = str_starts_with($level, 'ghorfeAdmin') ? 'ghorfeAdmin' : $level;

        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => $roleName === 'superAdmin' ? 'سوپر ادمین' : 'ادمین کسب‌وکار']
        );

        $user->roles()->syncWithoutDetaching([$role->id]);
    }

    /** Create or update a test business by its (test-only) en_title. */
    private function upsertBusiness(
        string $enTitle,
        string $title,
        int $ownerId,
        ?int $shahinPackageId,
        bool $smsContractAccepted
    ): GhorfeOnlineList {
        return GhorfeOnlineList::updateOrCreate(
            ['en_title' => $enTitle],
            [
                'user_id' => $ownerId,
                'template_id' => \Illuminate\Support\Facades\DB::table('templates')->value('id'),
                'title' => $title,
                'slug' => $enTitle,
                'type_work' => 'service',
                'type_sales' => 'all',
                'call' => '02100000000',
                'address' => 'آدرس تستی برای QA ماژول پیامک',
                'map_iframe_link' => '',
                'google_map_link' => '',
                'waze_map_link' => '',
                'copyright' => '',
                'index_title' => '',
                'index_description' => '',
                'footer_tag' => [],
                'sms_code' => [],
                'postal_code' => '0000000000',
                'status' => 1,
                'domain_active' => $enTitle . '.test.local',
                'lat' => 35.7000000,
                'lon' => 51.4000000,
                'shahin_package_id' => $shahinPackageId,
                'sms_contract_accepted' => $smsContractAccepted,
                'sms_contract_accepted_at' => $smsContractAccepted ? now() : null,
                'sms_contract_signature_name' => $smsContractAccepted ? 'تست خودکار QA' : null,
            ]
        );
    }

    /**
     * Link a user to a business as its staff/owner the same way
     * EmployeeController::store does: one `employees` row keyed by user_id,
     * which is what AdminAuthService::getGhorfe() (the ghorfe() helper) and
     * EnsureSmsContractSigned read to resolve "the current business".
     */
    private function attachEmployee(User $user, ?GhorfeOnlineList $business, string $businessPhone): Employee
    {
        return $this->upsertEmployeeRow($user, [
            'ghorfe_online_list_id' => $business?->id,
            'status' => 1,
            'business_phone' => $businessPhone,
            'show_status' => 1,
        ]);
    }

    /**
     * `employees.id` has no real auto_increment in this DB (verified via
     * SHOW COLUMNS: Extra is empty) — a plain updateOrCreate()/insert leaves
     * every new row at id=0, and since there's no PRIMARY KEY/unique
     * constraint either, nothing stops multiple rows from silently
     * colliding on id 0. Pre-existing DB wart, out of scope to fix
     * generally, but our own test rows need a real unique id to be usable
     * at all, so we assign one by hand.
     */
    private function upsertEmployeeRow(User $user, array $attributes): Employee
    {
        $employee = Employee::firstOrNew(['user_id' => $user->id]);

        if (!$employee->exists || (int) $employee->id === 0) {
            $employee->id = (int) (Employee::where('id', '>', 0)->max('id')) + 1;
        }

        $employee->fill($attributes);
        $employee->save();

        return $employee;
    }

    /** An advertising sender number assigned to a business (never OTP: the app allows only one OTP number system-wide). */
    private function upsertAdvertisingSenderNumber(string $number, string $panel): SenderNumber
    {
        // firstOrCreate: existing rows keep their panel (e.g. legacy TestSmsPanel, also simulated).
        return SenderNumber::firstOrCreate(
            ['number' => $number],
            [
                'type' => 'advertising',
                'sms_panel' => $panel,
                'status' => 'active',
                'description' => 'Test sender number created by sms:create-test-users',
            ]
        );
    }

    /** Give the business a non-zero advertising balance, only crediting once. */
    private function ensureAdvertisingBalance(GhorfeOnlineList $business, float $amount): AdvertisingBalance
    {
        $balance = AdvertisingBalance::firstOrCreate([
            'ghorfe_online_list_id' => $business->id,
        ]);

        if ((float) $balance->balance <= 0.0) {
            $balance->lockAndCredit($amount);
        }

        return $balance->refresh();
    }

    /** Give the business a non-zero OTP balance, only crediting once. */
    private function ensureOtpBalance(GhorfeOnlineList $business, float $amount): OtpBalance
    {
        $balance = OtpBalance::firstOrCreate(['ghorfe_online_list_id' => $business->id]);

        if ((float) $balance->balance <= 0.0) {
            $balance->lockAndCredit($amount);
        }

        return $balance->refresh();
    }

    /**
     * Fallback only: a package with the "پیامک" category attached, for a
     * local DB that has no shahin_packages at all yet.
     */
    private function makePackageWithSms(): ShahinPackage
    {
        $package = $this->upsertPackage('sms-test-package-with-sms', [
            'name' => 'پکیج تستی (با پیامک)',
            'description' => 'پکیج تستی ساخته‌شده توسط sms:create-test-users',
        ]);

        $this->ensurePackageHasSmsCategory($package);

        return $package;
    }

    /**
     * A package every OTHER category is attached to, but deliberately NOT
     * "پیامک" — this is Business B's package, and is what reproduces "a
     * business whose package doesn't grant SMS" regardless of how many
     * packages already got backfilled by 2026_10_11_000004.
     */
    private function makePackageWithoutSms(): ShahinPackage
    {
        $package = $this->upsertPackage('sms-test-package-without-sms', [
            'name' => 'پکیج تستی (بدون پیامک)',
            'description' => 'پکیج تستی ساخته‌شده توسط sms:create-test-users - بدون دسترسی پیامک',
        ]);

        $smsCategoryId = CategoryPermission::where('name', 'پیامک')->value('id');

        $otherCategoryIds = CategoryPermission::when($smsCategoryId, fn ($q) => $q->where('id', '!=', $smsCategoryId))
            ->pluck('id');

        $package->categoryPermission()->sync($otherCategoryIds);

        return $package;
    }

    /**
     * ShahinPackage::$fillable is only ['name','slug','description','status','features'],
     * missing several NOT NULL, no-default columns the schema actually has (type, price,
     * duration_days, is_active, image) — ShahinPackagesSeeder sidesteps this by inserting
     * through DB::table() directly. updateOrCreate() would silently drop those attributes
     * (no MassAssignmentException is configured) and then fail at the DB with "price/image
     * cannot be null", so this uses forceFill() instead.
     */
    private function upsertPackage(string $slug, array $attributes): ShahinPackage
    {
        $package = ShahinPackage::where('slug', $slug)->first() ?? new ShahinPackage();

        $package->forceFill($attributes + [
            'slug' => $slug,
            'type' => 'general',
            'price' => 0,
            'duration_days' => 3650,
            'is_active' => true,
            'image' => 'packages/test.png',
        ])->save();

        return $package;
    }

    private function ensurePackageHasSmsCategory(ShahinPackage $package): void
    {
        $smsCategoryId = CategoryPermission::where('name', 'پیامک')->value('id');

        if ($smsCategoryId && ! $package->categoryPermission()->where('category_permissions.id', $smsCategoryId)->exists()) {
            $package->categoryPermission()->syncWithoutDetaching([$smsCategoryId]);
        }
    }

    private function seedSampleMessages(GhorfeOnlineList $businessA, SenderNumber $senderNumber): void
    {
        (new \Database\Seeders\SmsMessageGroupSeeder())->run();

        $maliGroup = \App\Models\SmsMessageGroup::where('slug', 'mali')->first();
        $poshtibaniGroup = \App\Models\SmsMessageGroup::where('slug', 'poshtibani')->first();

        if ($maliGroup && ! \App\Models\SmsMessage::where('tracking_id', 'TRK-CONF-001')->exists()) {
            \App\Models\SmsMessage::create([
                'ghorfe_online_list_id' => $businessA->id,
                'direction' => 'outbound',
                'service_type' => 'advertising',
                'sender_number_id' => $senderNumber->id,
                'sender_phone' => $senderNumber->number,
                'recipient_phone' => '09121112233',
                'sms_message_group_id' => $maliGroup->id,
                'content' => 'مبلغ ۵,۰۰۰,۰۰۰ ریال به شماره شبا IR123456789 واریز شد (سند مالی محرمانه)',
                'character_count' => 70,
                'page_count' => 1,
                'cost' => 500,
                'status' => 'delivered',
                'tracking_id' => 'TRK-CONF-001',
            ]);
        }

        if ($poshtibaniGroup && ! \App\Models\SmsMessage::where('tracking_id', 'TRK-NORM-002')->exists()) {
            \App\Models\SmsMessage::create([
                'ghorfe_online_list_id' => $businessA->id,
                'direction' => 'outbound',
                'service_type' => 'advertising',
                'sender_number_id' => $senderNumber->id,
                'sender_phone' => $senderNumber->number,
                'recipient_phone' => '09129998877',
                'sms_message_group_id' => $poshtibaniGroup->id,
                'content' => 'سلام، درخواست پشتیبانی شما با موفقیت ثبت شد.',
                'character_count' => 45,
                'page_count' => 1,
                'cost' => 500,
                'status' => 'delivered',
                'tracking_id' => 'TRK-NORM-002',
            ]);
        }
    }

    /** @param array<int, array{email: string, role: string, business: string, note: string}> $rows */
    private function printSummary(array $rows): void
    {
        $this->newLine();
        $this->info('SMS module test users ready (password for all: ' . self::PASSWORD . ')');
        $this->table(
            ['Email', 'Password', 'Role', 'Business', 'Purpose'],
            array_map(fn ($row) => [
                $row['email'],
                self::PASSWORD,
                $row['role'],
                $row['business'],
                $row['note'],
            ], $rows)
        );
    }
}
