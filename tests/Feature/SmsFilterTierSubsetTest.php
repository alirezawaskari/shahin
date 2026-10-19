<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Sms\SmsAccessTierService;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * The group-SMS audience filter modal shows only the fields the sender's tier may use; fields
 * above the tier are not rendered at all (so they can never be submitted).
 */
class SmsFilterTierSubsetTest extends TestCase
{
    private const MODAL = 'Admin.callcenter.partials.filter-modal';
    private const FIELDS = 'Admin.callcenter.partials.filter-fields';
    private const HINT = 'فیلترهای بیشتر برای سطوح بالاتر در دسترس است';

    protected function setUp(): void
    {
        parent::setUp();
        // The composers load the requests page's reference data (DB); the markup under test does not need it.
        app('events')->forget('composing: ' . self::MODAL);
        app('events')->forget('composing: ' . self::FIELDS);
        app('events')->forget('composing: Admin.*'); // the sidebar menu composer (DB) is irrelevant here
    }

    private function actAs(string $tier, bool $superAdmin = false): void
    {
        $user = new class extends User {
            public bool $fakeSuper = false;

            public function isSuperAdmin(): bool
            {
                return $this->fakeSuper;
            }
        };
        $user->forceFill(['id' => 1, 'access_tier' => $tier, 'level' => 'admin']);
        $user->fakeSuper = $superAdmin;
        Auth::setUser($user);
    }

    private function modal(string $tier, bool $superAdmin = false): string
    {
        $this->actAs($tier, $superAdmin);

        return view(self::MODAL)->render();
    }

    public function test_map_assigns_required_tiers(): void
    {
        $map = app(SmsAccessTierService::class)->requestFilterFieldTiers();

        $this->assertSame('silver', $map['name']);
        $this->assertSame('silver', $map['status']);
        $this->assertSame('diamond', $map['product_category']);
        $this->assertSame('diamond', $map['service_items']);
        $this->assertSame('diamond', $map['product_attribute']);
        $this->assertSame('titanium', $map['organization']);
        $this->assertSame('titanium', $map['representation']);
    }

    public function test_visible_fields_grow_with_the_tier(): void
    {
        $svc = app(SmsAccessTierService::class);
        $this->actAs('silver');
        $silver = $svc->requestFilterFieldsFor(Auth::user());
        $this->actAs('gold');
        $gold = $svc->requestFilterFieldsFor(Auth::user());
        $this->actAs('diamond');
        $diamond = $svc->requestFilterFieldsFor(Auth::user());
        $this->actAs('titanium');
        $titanium = $svc->requestFilterFieldsFor(Auth::user());

        $this->assertNotContains('product_category', $silver);
        $this->assertNotContains('product_category', $gold);
        $this->assertContains('product_category', $diamond);
        $this->assertNotContains('organization', $diamond);
        $this->assertContains('organization', $titanium);
        $this->assertGreaterThanOrEqual(count($silver), count($gold));
        $this->assertGreaterThan(count($gold), count($diamond));
        $this->assertGreaterThan(count($diamond), count($titanium));
    }

    /** @dataProvider lowerTiers */
    public function test_silver_and_gold_modal_hides_category_and_product_fields(string $tier): void
    {
        $html = $this->modal($tier);

        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="status[]"', $html);
        $this->assertStringContainsString('name="province"', $html);
        foreach (['product_category[]', 'service_category[]', 'sub_service_category[]', 'service_items[]', 'product[]', 'product_attribute[]'] as $field) {
            $this->assertStringNotContainsString('name="' . $field . '"', $html, "$field must not be rendered for $tier");
        }
        $this->assertStringContainsString(self::HINT, $html);
    }

    public static function lowerTiers(): array
    {
        return ['silver' => ['silver'], 'gold' => ['gold']];
    }

    /** @dataProvider upperTiers */
    public function test_diamond_and_titanium_modal_shows_everything_without_hint(string $tier): void
    {
        $html = $this->modal($tier, $tier === 'titanium');

        foreach (['product_category[]', 'service_category[]', 'sub_service_category[]', 'service_items[]', 'product[]', 'product_attribute[]', 'name', 'status[]'] as $field) {
            $this->assertStringContainsString('name="' . $field . '"', $html, "$field should be rendered for $tier");
        }
        $this->assertStringNotContainsString(self::HINT, $html);
        // The modal never carries the business picker or the representation filter.
        $this->assertStringNotContainsString('name="representation"', $html);
    }

    public static function upperTiers(): array
    {
        return ['diamond' => ['diamond'], 'titanium' => ['titanium']];
    }

    public function test_fields_partial_defaults_to_showing_everything(): void
    {
        $this->actAs('silver');

        $html = view(self::FIELDS, ['withBusinessPicker' => false])->render();

        $this->assertStringContainsString('name="product_category[]"', $html);
        $this->assertStringContainsString('name="service_items[]"', $html);
        $this->assertStringContainsString('name="representation"', $html);
        $this->assertStringNotContainsString(self::HINT, $html);
    }

    public function test_server_enforcement_is_unchanged(): void
    {
        $svc = app(SmsAccessTierService::class);

        $this->assertSame('diamond', $svc->getRequiredTier(['request_filter' => ['product_category' => [3]]]));
        $this->assertSame('silver', $svc->getRequiredTier(['request_filter' => ['name' => 'x']]));
    }
}
