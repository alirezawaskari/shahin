<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;


class FactorsExport implements FromCollection , ShouldAutoSize ,WithHeadings
{
    use Exportable;
    private $factors;

    public function __construct($factors)
    {
        $this->factors = $factors;
    }

    public function collection()
    {
        $this->factors = $this->factors->get();
        $factors2 = $this->factors->map(function ($item) {
            $temp = [];
            $temp['date'] = verta($item->created_at)->format('%d %B ، %Y - H:i:s');
            $temp['tax'] =  $item->tax ? $item->tax : 0;
            $temp['price'] = $item->price - $item->tax;
            $temp['service'] = $this->setService($item);
            $temp['buyer'] = isset($item->user) ? $item->user->name.' '.$item->user->family.'-'.$item->user->melicode.'-'.$item->user->phone : '';
            $temp['seller'] = 'شرکت جانبازان آینده ساز شماره اقتصادی: 411495546645 شماره ثبت: 478000';
            return $temp;
        });

        return $factors2;

    }

    public function headings(): array
    {
        return ['تاریخ', 'ارزش افزوده', 'مبلغ' , 'خدمت', 'خریدار','فروشنده'];
    }
    public function setService($item)
    {
        $service = [];
        foreach ($item->factorItems as $factor_item){
            $service = isset( $factor_item->service) ? $factor_item->service->title : '';
        }
        return $service;
    }
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
