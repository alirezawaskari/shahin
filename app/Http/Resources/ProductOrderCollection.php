<?php

namespace App\Http\Resources;

use App\Models\Factor;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * One row per product order (a paid factor carrying کالا rows) for the orders datatable.
 *
 * `$total_price` is the across-all-pages figure shown under the table; it is computed by the
 * controller BEFORE pagination, because paginate() leaves LIMIT/OFFSET on the builder.
 */
class ProductOrderCollection extends ResourceCollection
{
    /**
     * @var int|float total of every order matching the filter, across all pages
     */
    public $total_price;

    /**
     * @param int|float $total_price
     * @param $collection
     */
    public function __construct($total_price, $collection)
    {
        parent::__construct($collection);
        $this->total_price = $total_price;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function (Factor $item) {
                $address = $item->factorAddress;

                return [
                    'sort_by' => 'date',
                    'id' => $item->id,
                    'resnumber' => $item->resnumber,
                    'name' => trim(optional($item->user)->name . ' ' . optional($item->user)->family),
                    'phone' => optional($item->user)->phone,
                    'products' => $this->setProducts($item),
                    'products_count' => $this->setProductsCount($item),
                    'price' => number_format($item->price),
                    'shipping_cost' => number_format($item->shipping_cost),
                    'total_price' => number_format($item->order_total_price),
                    'date' => verta($item->created_at)->format('Y/m/d H:i'),
                    'shipping_status' => (int) $item->shipping_status,
                    'shipping_status_text' => Factor::SHIPPING_STATUS[(int) $item->shipping_status] ?? '',
                    'address' => $address ? $address->full_address : 'ثبت نشده',
                    'recipient' => $address ? $address->recipient : '',
                    'tracking_code' => $item->tracking_code ?: '-',
                    'shipped_at' => $item->shipped_at ? verta($item->shipped_at)->format('Y/m/d H:i') : '-',
                    'show_url' => route('productOrder.show', [$item->resnumber]),
                ];
            }),
            'meta' => [
                'sum_price' => number_format($this->collection->sum(fn(Factor $item) => $item->order_total_price)),
                'total_price' => number_format($this->total_price),
            ],
        ];
    }

    /**
     * Every ordered کالا of the order, as one cell.
     *
     * @param Factor $item
     * @return string
     */
    private function setProducts(Factor $item): string
    {
        $titles = [];

        foreach ($item->factorItems as $factor_item) {
            foreach ($factor_item->products as $product) {
                $name = optional($product->product)->name;
                if ($name) {
                    $titles[] = $product->count > 1 ? $name . ' (' . $product->count . ')' : $name;
                }
            }
        }

        return $titles ? implode(' - ', $titles) : '-';
    }

    /**
     * @param Factor $item
     * @return int
     */
    private function setProductsCount(Factor $item): int
    {
        $count = 0;

        foreach ($item->factorItems as $factor_item) {
            $count = $count + (int) $factor_item->products->sum('count');
        }

        return $count;
    }
}
