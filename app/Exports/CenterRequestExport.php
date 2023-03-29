<?php

namespace App\Exports;

use App\Models\sms;
use App\Models\User;
use Hekmatinasser\Verta\Verta;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use function PHPSTORM_META\map;

class CenterRequestExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    use Exportable;
    private $center_requests;

    /**
     * CallcenterExport constructor.
     * @param $center_requests
     */
    public function __construct($center_requests)
    {
        $this->center_requests = $center_requests;
    }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $this->center_requests = $this->center_requests->get();
        $map_center_requests = $this->center_requests->map(function ($item) {
            $temp = [];
            $temp['name'] = $item->name;
            $temp['subject'] = $item->subject;
            $temp['phone'] = $item->phone;
            $temp['created_at'] = verta($item->created_at)->format('%d %B ، %Y - H:i:s');

            return $temp;
        });
        return $map_center_requests;
    }

    public function headings(): array
    {
        return ['نام' , 'موضوع' , 'شماره تماس','تاریخ ثبت'];
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
