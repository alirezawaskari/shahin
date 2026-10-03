<?php

namespace App\Services\V1\Admin;

use App\Models\GhorfeOnlineList;
use App\Models\Product;
use App\Models\SpecialSaleGroup;
use App\Models\SpecialSaleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * «شگفت‌انگیز» از پنل: افزودن یک یا چند کالا با درصد تخفیف و زمان خروج، و حذف دستی.
 *
 * داده همان special_sale_groups / special_sale_items است که API کالا می‌خواند
 * (HomeService::getSuggestedItems — گروه فعال و در بازه زمانی). زمان خروج روی گروه است،
 * پس هر ثبت یک گروه تازه می‌سازد تا هر انتخاب زمان پایان خودش را داشته باشد؛ با رسیدن
 * ends_at کالا خودبه‌خود از شگفت‌انگیز خارج می‌شود و کاری برای cron نمی‌ماند.
 *
 * کالایی که دوباره اضافه شود از گروه قبلی‌اش برداشته می‌شود تا هم‌زمان در دو گروه با دو
 * درصد مختلف نباشد.
 */
class SpecialSaleService
{
    /** کالاهایی که این کسب‌وکار می‌تواند به شگفت‌انگیز ببرد. */
    public function selectableProducts(?GhorfeOnlineList $ghorfe): Collection
    {
        $query = $ghorfe ? $ghorfe->products() : Product::query();

        return $query->where('products.type', 1)
            ->select('products.id', 'products.name')
            ->orderByDesc('products.id')
            ->get();
    }

    /** ردیف‌های شگفت‌انگیزِ هنوز تمام‌نشده این کسب‌وکار، با کالا و گروه. */
    public function currentItems(?GhorfeOnlineList $ghorfe): Collection
    {
        return SpecialSaleItem::query()
            ->whereHas('group', fn ($q) => $this->scopeToGhorfe($q, $ghorfe)->notEnded())
            ->with(['product:id,name', 'group'])
            ->get()
            ->sortBy(fn (SpecialSaleItem $item) => optional($item->group->ends_at)->timestamp ?? PHP_INT_MAX)
            ->values();
    }

    /**
     * @param int[] $product_ids
     */
    public function add(?GhorfeOnlineList $ghorfe, array $product_ids, float $percentage, Carbon $ends_at): SpecialSaleGroup
    {
        return DB::transaction(function () use ($ghorfe, $product_ids, $percentage, $ends_at) {
            $group = SpecialSaleGroup::create([
                'ghorfe_online_list_id' => optional($ghorfe)->id,
                'title'     => 'شگفت‌انگیز ' . verta()->format('Y/m/d H:i'),
                'is_active' => true,
                'starts_at' => now(),
                'ends_at'   => $ends_at,
            ]);

            // همین کالاها در گروه‌های باز قبلی همین کسب‌وکار جایگزین می‌شوند
            $this->openItemsOf($ghorfe, $product_ids)->each->delete();

            foreach ($product_ids as $product_id) {
                $group->items()->create([
                    'product_id' => $product_id,
                    'percentage' => $percentage,
                ]);
            }

            $this->dropEmptyGroups($ghorfe);

            return $group;
        });
    }

    /** خروج دستی یک کالا از شگفت‌انگیز (پیش از رسیدن زمان پایان). */
    public function remove(SpecialSaleItem $item): void
    {
        DB::transaction(function () use ($item) {
            $group = $item->group;
            $item->delete();

            if ($group && !$group->items()->exists()) {
                $group->delete();
            }
        });
    }

    /** آیا این ردیف مال این کسب‌وکار است (برای جلوگیری از حذف ردیف کسب‌وکار دیگر). */
    public function belongsTo(SpecialSaleItem $item, ?GhorfeOnlineList $ghorfe): bool
    {
        return $item->group && (!$ghorfe || (int) $item->group->ghorfe_online_list_id === (int) $ghorfe->id);
    }

    private function openItemsOf(?GhorfeOnlineList $ghorfe, array $product_ids): Collection
    {
        return SpecialSaleItem::whereIn('product_id', $product_ids)
            ->whereHas('group', fn ($q) => $this->scopeToGhorfe($q, $ghorfe)->notEnded())
            ->get();
    }

    private function dropEmptyGroups(?GhorfeOnlineList $ghorfe): void
    {
        $this->scopeToGhorfe(SpecialSaleGroup::query(), $ghorfe)
            ->whereDoesntHave('items')
            ->get()
            ->each->delete();
    }

    /** کاربر بدون کسب‌وکار جاری (ستاد) همه گروه‌ها را می‌بیند. */
    private function scopeToGhorfe($query, ?GhorfeOnlineList $ghorfe)
    {
        return $ghorfe ? $query->where('ghorfe_online_list_id', $ghorfe->id) : $query;
    }
}
