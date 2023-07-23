<?php

namespace App\Exports;

use App\Models\Check;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;


class CheckExport implements FromCollection , ShouldAutoSize ,WithHeadings
{
    use Exportable;

    /**
     * @var
     */
    private $checks;

    /**
     * @param $checks
     */
    public function __construct($checks)
    {
        $this->checks = $checks;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {

        $i=0;
        $this->checks = $this->checks->get();
        $checks = $this->checks->map(function ($item,$i) {
            $applicant = User::find($item->applicantable_id);
            $exporter = User::find($item->exportable_id);
            $pay_to = User::find($item->paytoable_id);
            $temp = [];

            $temp['number'] = ++$i;
            $temp['date'] = verta($item->date)->format('%Y/%m/%d ');

            $temp['applicant'] =$applicant->name.' '.$applicant->family;
            $temp['applicant_phone'] =$applicant->phone;

            $temp['exporter'] = $exporter->name.' '.$exporter->family;
            $temp['exporter_phone'] = $exporter->phone;

            $temp['pay_to'] = $pay_to->name.' '.$pay_to->family;
            $temp['pay_to_phone'] =$pay_to->phone;

            $temp['amount'] =number_format($item->amount);
            return $temp;
        });

        return $checks;

    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return [
            '#',
            ' تاریخ وصول',
            'متقاضی',
            'موبایل متقاضی',
            'صادر کننده' ,
            'موبایل صادر کننده' ,
            'در وجه' ,
            'موبایل صادر شده در وجه' ,
            'مبلغ(ریال)',
        ];
    }

    /**
     * @param $item
     * @return string
     */
    public function setStatus($item)
    {
        return Check::status($item);
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
