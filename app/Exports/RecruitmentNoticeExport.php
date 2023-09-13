<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class RecruitmentNoticeExport implements FromCollection , ShouldAutoSize ,WithHeadings{

    use Exportable;

    /**
     * @var
     */
    private $recruitment_notice;

    /**
     * @param $recruitment_notice
     */
    public function __construct($recruitment_notice)
    {
        $this->recruitment_notice = $recruitment_notice;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {

        $this->recruitment_notice = $this->recruitment_notice->get();

        $recruitment_notice = $this->recruitment_notice->map(function ($item) {
            $temp = [];
            $temp['title'] = $item->title;
            $temp['province'] = $item->province->name;
            $temp['representation'] = $item->representation ? $item->representation->fa_name:'';

            return $temp;
        });

        return $recruitment_notice;

    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return ['عنوان', 'استان' ,'نمایندگی'];
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
