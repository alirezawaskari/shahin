<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class UsersExport implements FromCollection , ShouldAutoSize ,WithHeadings
{
    use Exportable;

    /**
     * @var
     */
    private $users;

    /**
     * @param $users
     */
    public function __construct($users)
    {
        $this->users = $users;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $this->users = $this->users->get();
        $userfinal = $this->users->map(function ($item) {
            $temp = [];
            $temp['user_name'] = $item->name;
            $temp['user_family'] = $item->family;
            $temp['user_shenasnamecode'] = $item->shenasnamecode;
            $temp['user_melicode'] = $item->melicode;
            $temp['user_phone'] = $item->phone;
            $temp['user_call'] = $item->call;
            $temp['user_postalcode'] = $item->postalcode;
            $temp['user_address'] = $item->address;
            $temp['user_date'] = verta($item->created_at)->format('%d %B ، %Y - H:i:s');
            if ($item->factors()->whereStatus(true)->count() > 0){
                foreach ($item->factors as $factor) {
                    $services [] = $factor->service ? $factor->service->title : $factor->factorable_type;
                }
                $temp['services'] = implode('|' ,$services);
            }

            return $temp;
        });

        return $userfinal;

    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return ['نام', 'نام خانوادگی', 'شناسنامه' , 'تاریخ تولد' , 'کد ملی' , 'موبایل' , 'شماره ثابت' , 'کد پستی' , 'آدرس','تاریخ عضویت','خدمات خریداری شده'];
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
