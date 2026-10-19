<?php

namespace App\Services\Sms;

use App\Models\Category;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

/**
 * Turns catalog ids into "Top Category > Subcategory > Service Name" labels for SMS logs/reports.
 *
 * Catalog shape: categories form a tree (categories.parent_id; roots have a null parent), a service
 * belongs to one category (services.category_id), a product to many (category_product), and a request
 * (callcenters row) is tied to its service(s)/category through the polymorphic callcenterables table.
 * Everything is batch-loaded and memoized on the instance, so resolving a page of messages costs a
 * fixed handful of queries regardless of its size. Missing ids degrade to a plain "#id" label.
 */
class CatalogBreadcrumbResolver
{
    public const SEPARATOR = ' > ';

    /** @var array<int, array{title: string, parent_id: int|null}|null> */
    protected array $categories = [];

    /** @var array<int, array{title: string, category_id: int|null}|null> */
    protected array $services = [];

    /** @var array<int, string> memoized callcenter id => label */
    protected array $callcenterLabels = [];

    /** Pulls the given category ids and all their ancestors into the memo, one query per tree level. */
    protected function loadCategoryChains(array $ids): void
    {
        $pending = array_values(array_unique(array_filter(array_map('intval', $ids))));

        for ($guard = 0; $guard < 12 && $pending; $guard++) {
            $pending = array_values(array_filter($pending, fn ($id) => !array_key_exists($id, $this->categories)));
            if (!$pending) {
                break;
            }

            $rows = Category::query()->withTrashed()->whereIn('id', $pending)->get(['id', 'title', 'parent_id'])->keyBy('id');
            $next = [];
            foreach ($pending as $id) {
                $row = $rows->get($id);
                $this->categories[$id] = $row ? ['title' => (string) $row->title, 'parent_id' => $row->parent_id ? (int) $row->parent_id : null] : null;
                if ($row && $row->parent_id) {
                    $next[] = (int) $row->parent_id;
                }
            }
            $pending = $next;
        }
    }

    protected function loadServices(array $ids): void
    {
        $ids = array_values(array_filter(array_unique(array_map('intval', $ids)), fn ($id) => $id && !array_key_exists($id, $this->services)));
        if (!$ids) {
            return;
        }

        $rows = Service::query()->withTrashed()->whereIn('id', $ids)->get(['id', 'title', 'category_id'])->keyBy('id');
        foreach ($ids as $id) {
            $row = $rows->get($id);
            $this->services[$id] = $row ? ['title' => (string) $row->title, 'category_id' => $row->category_id ? (int) $row->category_id : null] : null;
        }
        $this->loadCategoryChains(array_filter(array_column(array_filter($this->services), 'category_id')));
    }

    /** Root-to-leaf category titles for a (already loaded) category id. */
    protected function chainTitles(?int $categoryId): array
    {
        $titles = [];
        $seen = [];
        while ($categoryId && !isset($seen[$categoryId]) && ($node = $this->categories[$categoryId] ?? null)) {
            $seen[$categoryId] = true;
            array_unshift($titles, $node['title']);
            $categoryId = $node['parent_id'];
        }

        return $titles;
    }

    /** @return array<int, string> category id => breadcrumb ('#id' for unknown ids) */
    public function forCategories(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $this->loadCategoryChains($ids);

        $out = [];
        foreach ($ids as $id) {
            $titles = $this->chainTitles($id);
            $out[$id] = $titles ? implode(self::SEPARATOR, $titles) : "#{$id}";
        }

        return $out;
    }

    /** @return array<int, string> service id => breadcrumb */
    public function forServices(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $this->loadServices($ids);

        $out = [];
        foreach ($ids as $id) {
            $svc = $this->services[$id] ?? null;
            $out[$id] = $svc
                ? implode(self::SEPARATOR, [...$this->chainTitles($svc['category_id']), $svc['title']])
                : "#{$id}";
        }

        return $out;
    }

    /** @return array<int, string> product id => breadcrumb (first category's chain, then the product title) */
    public function forProducts(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) {
            return [];
        }

        $products = Product::query()->withTrashed()->whereIn('id', $ids)->get(['id', 'title'])->keyBy('id');
        $pivot = DB::table('category_product')->whereIn('product_id', $ids)->orderBy('id')->get(['product_id', 'category_id'])
            ->unique('product_id')->pluck('category_id', 'product_id');
        $this->loadCategoryChains($pivot->all());

        $out = [];
        foreach ($ids as $id) {
            $product = $products->get($id);
            $out[$id] = $product
                ? implode(self::SEPARATOR, [...$this->chainTitles($pivot->get($id)), (string) $product->title])
                : "#{$id}";
        }

        return $out;
    }

    /**
     * Request (callcenters row) id => breadcrumb of its service, or of its category when it has no
     * service; "#id" when the request has no catalog link at all.
     *
     * @return array<int, string>
     */
    public function forRequests(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $todo = array_values(array_filter($ids, fn ($id) => !isset($this->callcenterLabels[$id])));

        if ($todo) {
            $links = DB::table('callcenterables')->whereIn('callcenter_id', $todo)->orderBy('id')
                ->get(['callcenter_id', 'callcenterable_id', 'callcenterable_type']);

            $serviceLinks = $links->where('callcenterable_type', Service::class)->unique('callcenter_id');
            $categoryLinks = $links->where('callcenterable_type', Category::class)->unique('callcenter_id');
            $serviceLabels = $this->forServices($serviceLinks->pluck('callcenterable_id')->all());
            $categoryLabels = $this->forCategories($categoryLinks->pluck('callcenterable_id')->all());

            foreach ($todo as $id) {
                $svc = $serviceLinks->firstWhere('callcenter_id', $id);
                $cat = $categoryLinks->firstWhere('callcenter_id', $id);
                $this->callcenterLabels[$id] = $svc
                    ? $serviceLabels[(int) $svc->callcenterable_id]
                    : ($cat ? $categoryLabels[(int) $cat->callcenterable_id] : "#{$id}");
            }
        }

        return array_intersect_key($this->callcenterLabels, array_flip($ids));
    }

    public function forRequest(?int $id): ?string
    {
        return $id ? ($this->forRequests([$id])[$id] ?? "#{$id}") : null;
    }

    /**
     * Sets a non-persisted `request_breadcrumb` on each SMS message (its callcenter_id, else request_id),
     * resolving the whole batch at once. Returns the same collection/paginator for chaining.
     */
    public function annotateMessages(iterable $messages): iterable
    {
        $ref = fn ($m) => (int) ($m->callcenter_id ?: $m->request_id);
        $labels = $this->forRequests(collect($messages)->map($ref)->all());

        foreach ($messages as $message) {
            $id = $ref($message);
            $message->setAttribute('request_breadcrumb', $id ? ($labels[$id] ?? "#{$id}") : null);
        }

        return $messages;
    }
}
