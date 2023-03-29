<?php

namespace App\Exports;

use App\Models\Agency;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class NewsletterExport implements FromCollection , ShouldAutoSize ,WithHeadings{

    use Exportable;
    private $emails;

    public function __construct($emails)
    {
        $this->emails = $emails;
    }

    public function collection()
    {
        $emails = $this->emails->map(function ($item) {
            $temp = [];
            $temp['email'] = $item->email;
            $temp['date'] = $item->created_at;

            return $temp;
        });

        return $emails;
    }

    public function headings(): array
    {
        return ['ایمیل','تاریخ ثبت'];
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
