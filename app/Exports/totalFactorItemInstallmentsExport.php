<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;


class totalFactorItemInstallmentsExport implements FromCollection , ShouldAutoSize ,WithHeadings
{
    use Exportable;

    /**
     * @var
     */
    private $total_factor_item_installments;

    /**
     * @param $total_factor_item_installments
     */
    public function __construct($total_factor_item_installments)
    {
        $this->total_factor_item_installments = $total_factor_item_installments;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $this->total_factor_item_installments = $this->total_factor_item_installments->get();
        $total_factor_item_installments2 = $this->total_factor_item_installments->map(function ($item) {
            $temp = [];
            $temp['user_name'] = $item->totalFactorItem->factor->user->name;
            $temp['user_family'] = $item->totalFactorItem->factor->user->family;
            $temp['user_phone'] = $item->totalFactorItem->factor->user->phone;
			$temp['price'] = number_format($item->price);
            $temp['date'] = verta($item->date)->format('Y/m/d');
            return $temp;
        });

        return $total_factor_item_installments2;

    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return ['نام', 'نام خانوادگی', 'موبایل', 'مبلغ قسط(ریال)','سر رسید قسط'];
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
