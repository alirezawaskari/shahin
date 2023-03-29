<?php

namespace App\Exports;

use App\Models\Check;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;


class CheckExport implements FromCollection , ShouldAutoSize ,WithHeadings
{
    use Exportable;
    private $checks;

    public function __construct($checks)
    {
        $this->checks = $checks;
    }

    public function collection()
    {
        $i=0;
        $this->checks = $this->checks->get();
        $checks = $this->checks->map(function ($item,$i) {
            $temp = [];
            $temp['number'] = ++$i;
            $temp['date'] = verta($item->date)->format('%Y/%m/%d ');
            $temp['exporter'] = $item->exporter;
            $temp['pay_to'] =$item->pay_to;
            $temp['amount'] =number_format($item->amount);
            return $temp;
        });

        return $checks;

    }

    public function headings(): array
    {
        return [
            '#',
            'تاریخ',
            'صادر کننده',
            'در وجه' ,
            'مبلغ(ریال)',
        ];
    }

    public function setStatus($item)
    {
        return Check::status($item);
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
