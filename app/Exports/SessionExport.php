<?php

namespace App\Exports;

use App\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;


class SessionExport implements FromCollection , ShouldAutoSize ,WithHeadings
{
    use Exportable;

    /**
     * @var
     */
    private $sessions;

    /**
     * @param $sessions
     */
    public function __construct($sessions)
    {
        $this->sessions = $sessions;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $i=0;
        $this->sessions = $this->sessions->get();
        $factorItems2 = $this->sessions->map(function ($item,$i) {
            $temp = [];
            $temp['number'] = ++$i;
            $temp['full_name'] = isset($item->factorItem) && isset($item->factorItem->factor) ? ($item->factorItem->factor->user->name.' '.$item->factorItem->factor->user->family) : 'ندارد';
            $temp['melli_code'] = isset($item->factorItem) && isset($item->factorItem->factor) ? $item->factorItem->factor->user->melicode : '';
            $temp['price'] = $item->price;
			$temp['date_and_time'] = verta($item->date)->format('%Y/%m/%d ');
            return $temp;
        });

        return $factorItems2;

    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return ['سریال','نام و نام خانوادگی', 'کد ملی' ,'مبلغ','تاریخ' ];
    }

    /**
     * @param $item
     * @return mixed
     */
    public function setStatus($item)
    {
        return Session::status($item);
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
