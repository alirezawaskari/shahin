<?php

namespace App\Exports;

use App\Models\year;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;


class FactorItemsExport implements FromCollection , ShouldAutoSize ,WithHeadings
{
    use Exportable;
    private $factorItems;

    public function __construct($factorItems)
    {
        $this->factorItems = $factorItems;
    }

    public function collection()
    {
        $this->factorItems = $this->factorItems->get();
        $factorItems2 = $this->factorItems->map(function ($item) {
            $temp = [];
            $temp['user_name'] = $item->factor->user->name;
            $temp['user_family'] = $item->factor->user->family;
            $temp['user_phone'] = $item->factor->user->phone;
            $temp['user_service'] = $this->setItems($item);
			$temp['total_price'] = number_format($item->total_price);
            $temp['laon'] = number_format($item->installments_price * $item->installments_num);
            $temp['prepayment'] = number_format($item->prepayment['price']);
            $temp['installment_price'] = number_format($item->installments_price);
            $temp['installment_num'] = $item->installments_num;
            $temp['installments_num_paid'] = $item->FactorItemInstallments ? count($item->FactorItemInstallments->where('status' , true)):'';
            $temp['start_end_date'] = $this->startEndDate($item);
            return $temp;
        });

        return $factorItems2;

    }

    public function headings(): array
    {
        return ['نام', 'نام خانوادگی', 'موبایل', 'خدمات ','جمع کل خرید' , 'تسهیلات(ریال)' , 'پیش پرداخت(ریال)' , 'مبلغ قسط(ریال)', 'تعداد اقساط','تعداد اقساط پرداخت شده','تاریخ شروع و پایان'];
    }
    public function setAddress($item)
    {
        foreach ($item->factorItems as $factor_item){
            $temp = isset($factor_item->value) && isset($factor_item->value['address']) ? $factor_item->value['address'] : $item->user->address;
        }
        return $temp;
    }
    public function startEndDate($item)
    {
        if(isset($item->toalFactorItemInstallments))
        {
            $temp = verta($item->approval_date)->format('Y/m/d'). '-' .verta($item->toalFactorItemInstallments->last()->date)->format('Y/m/d');
            return $temp;
        }
    }
    public function setItems($item)
    {
        $temp = [];
        if(isset($item->factoritemable))
        {
            array_push($temp , isset($item->center->title) ? $item->factoritemable->app_title.' - '.$item->center->title : $item->factoritemable->app_title.' - تعیین نشده');
        }
        elseif(isset($item->category))
        {
            array_push($temp , isset($item->center->title) ? $item->category->title.' - '.$item->center->title : $item->category->title.' - تعیین نشده');
        }
        return implode(',' , $temp);
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
