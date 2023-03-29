<?php

namespace App\Exports;

use App\Models\Resumes;
use App\Models\year;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;


class ResumsExport implements FromCollection , ShouldAutoSize ,WithHeadings
{
    use Exportable;
    private $resumes;

    public function __construct($resumes)
    {
        $this->resumes = $resumes;
    }

    public function collection()
    {
        $this->resumes = $this->resumes->get();
        $resumes2 = $this->resumes->map(function ($item) {
            $temp = [];
            $temp['name'] = $item->name;
            $temp['phone'] =  $item->phone;
            $temp['job_title'] = isset($item->recruitment_notice) ? $item->recruitment_notice->title : 'ندارد';

            return $temp;
        });
        return $resumes2;

    }

    public function headings(): array
    {
        return [' نام', 'موبایل','عنوان شغلی'];
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
