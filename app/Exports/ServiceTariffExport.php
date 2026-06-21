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

    /**
     * @var
     */
    private $serviceTariffs;

    /**
     * @param $serviceTariffs
     */
    public function __construct($serviceTariffs)
    {
        $this->serviceTariffs = $serviceTariffs;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $i=0;
        $this->serviceTariffs = $this->serviceTariffs->get();
        $factorItems2 = $this->serviceTariffs->map(function ($item,$i) {
            $temp = [];
            $temp['number'] = ++$i;
            $temp['name'] = $item->name ? $item->name : $item->title;
            $temp['purchase_price'] = $item->purchase_price;
            if(auth()->user()->isAbleTo('service_tariff_selling_price'))
            {
                $temp['selling_price'] = $item->price;
            }
            $temp['tariff'] = $item->tariff ? $item->tariff->title : 'ثبت نشده';
			$temp['created_at'] = verta($item->created_at)->format('%Y/%m/%d ');
            return $temp;
        });

        return $factorItems2;

    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        if(auth()->user()->isAbleTo('service_tariff_selling_price'))
        {
            return ['سریال','عنوان تعرفه', 'قیمت فروش شعبه (ریال)' , 'قیمت مصرف کننده (ریال)','آخرین ویرایش','تاریخ ثبت' ];
        }
        else
        {
            return ['سریال','عنوان تعرفه', 'قیمت فروش شعبه(ریال)' ,'آخرین ویرایش','تاریخ ثبت' ];
        }
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
