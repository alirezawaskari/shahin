<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;


class ServiceTariffExport implements FromCollection , ShouldAutoSize ,WithHeadings
{
    use Exportable;
    private $serviceTariffs;

    public function __construct($serviceTariffs)
    {
        $this->serviceTariffs = $serviceTariffs;
    }

    public function collection()
    {
        $i=0;
        $this->serviceTariffs = $this->serviceTariffs->get();
        $factorItems2 = $this->serviceTariffs->map(function ($item,$i) {
            $temp = [];
            $temp['number'] = ++$i;
            $temp['name'] = $item->name;
            $temp['price'] = $item->price;
            $temp['tariff'] = $item->tariff->title;
			$temp['created_at'] = verta($item->created_at)->format('%Y/%m/%d ');
            return $temp;
        });

        return $factorItems2;

    }

    public function headings(): array
    {
        return ['سریال','عنوان تعرفه', 'قیمت (ریال)' ,'آخرین ویرایش','تاریخ ثبت' ];
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
