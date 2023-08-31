<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class AgencyExport implements FromCollection , ShouldAutoSize ,WithHeadings{

    use Exportable;

    /**
     * @var
     */
    private $agencies;

    /**
     * @param $agencies
     */
    public function __construct($agencies)
    {
        $this->agencies = $agencies;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {

        $this->agencies = $this->agencies->get();
        $factors2 = $this->agencies->map(function ($item) {
            $temp = [];
            $temp['user_name'] = $item->name;
            $temp['user_melicode'] = $item->melicode;
            $temp['user_phone'] = $item->phone;
            $temp['user_province'] = $item->province;
            $temp['user_city'] = $item->city;
            $temp['user_description'] = $item->description;
            $temp['user_date'] = $item->created_at;

            return $temp;
        });

        return $factors2;

    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return ['نام', 'کد ملی' ,'موبایل','استان','شهر','توضیحات','تاریخ ثبت'];
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
