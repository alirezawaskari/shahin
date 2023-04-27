<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class NewsletterExport implements FromCollection , ShouldAutoSize ,WithHeadings{

    use Exportable;

    /**
     * @var
     */
    private $emails;

    /**
     * @param $emails
     */
    public function __construct($emails)
    {
        $this->emails = $emails;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
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

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return ['ایمیل','تاریخ ثبت'];
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
