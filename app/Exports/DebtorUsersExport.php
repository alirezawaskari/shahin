<?php

namespace App\Exports;

use App\year;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class DebtorUsersExport implements FromCollection , ShouldAutoSize ,WithHeadings
{
    use Exportable;
    private $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function collection()
    {
        $this->users = $this->users->get();
        $userfinal = $this->users->map(function ($item) {
            $temp = [];
            $temp['user_name'] = $item->name;
            $temp['user_family'] = $item->family;
            $temp['user_melicode'] = $item->melicode;
            $temp['user_phone'] = $item->phone;
            $temp['user_call'] = $item->call;

            return $temp;
        });

        return $userfinal;

    }

    public function headings(): array
    {
        return ['نام',
            'نام خانوادگی',
            'کد ملی' ,
            'موبایل' ,
            'شماره ثابت' ,
            ];
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
