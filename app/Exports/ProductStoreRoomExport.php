<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductStoreRoomExport implements FromCollection , ShouldAutoSize ,WithHeadings{

    use Exportable;

    /**
     * @var
     */
    private $products_store_room;

    /**
     * @param $products_store_room
     */
    public function __construct($products_store_room)
    {
        $this->products_store_room = $products_store_room;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $this->products_store_room = $this->products_store_room;
        $factors2 = $this->products_store_room->map(function ($item) {
            $temp = [];
            $temp['product_name'] = $item->name ;
            $temp['product_address'] = $item->pivot->storage_num.' - '.$item->pivot->row_num.' - '.$item->pivot->col_num;
            $temp['product_main_unit'] = $item->pivot->main_unit_id;
            $temp['product_type'] = $item->type ;
            $temp['product_initial_stock'] = $item->pivot->initial_stock ;
            $temp['product_stock'] = $item->pivot->stock ? $item->pivot->stock : 0;
            $temp['product_created'] = $item->pivot->created_at;

            return $temp;
        });

        return $factors2;

    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
//        return ['نام'];
        return ['نام', 'قفسه-سطر-ستون', 'واحد اصلی' , 'نوع محصول','موجودی اولیه','موجودی','تاریخ ثبت'];
    }

    /**
     * @return \Closure[]
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:W1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
            },
        ];
    }
}
