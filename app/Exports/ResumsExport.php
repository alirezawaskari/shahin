<?php

namespace App\Exports;

use App\Models\Resumes;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;


class ResumsExport implements FromCollection , ShouldAutoSize ,WithHeadings
{
    use Exportable;

    /**
     * @var
     */
    private $resumes;

    /**
     * @param $resumes
     */
    public function __construct($resumes)
    {
        $this->resumes = $resumes;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $this->resumes = $this->resumes->get();
        $resumes2 = $this->resumes->map(function ($item) {
            $temp = [];
            $temp['name'] = $item->name;
            $temp['phone'] =  $item->phone;
            $temp['job_title'] = isset($item->recruitment_notice) ? $item->recruitment_notice->title : 'ندارد';

            $temp['number_shenasname'] =  $item->detail['number_shenasname']??'';

            $temp['birth_certificate_issuing_place'] =  $item->detail['birth_certificate_issuing_place']??'';

            $temp['national_number'] =  $item->detail['national_number']??'';

            $temp['religion'] =  $item->detail['religion']??'';

            $temp['nationality'] =  $item->detail['nationality']??'';

            $temp['bime_history'] =  $item->detail['bime_history']??'';

            $temp['marital_status'] =  $item->detail['marital_status']??'';

            $temp['sex'] =  $item->detail['sex']??'';

            $temp['address'] =  $item->detail['address']??'';

            $temp['housing_situation'] =  $item->detail['housing_situation']??'';

            $temp['telephone'] =  $item->detail['telephone']??'';

            $temp['military_service_status'] =  $item->detail['military_service_status']??'';

            $temp['exemption_military_service_'] =  $item->detail['exemption_military_service_']??'';

            $temp['exemption_military_service_type'] =  $item->detail['exemption_military_service_type']??'';

            $temp['exemption_military_service_end_date'] =  $item->detail['exemption_military_service_end_date']??'';

            $temp['military_service_location'] =  $item->detail['military_service_location']??'';

            $temp['military_service_end_date'] =  $item->detail['military_service_end_date']??'';

            $temp['work_history'] =  $item->detail['work_history']??'';

            $temp['degree_of_education'] =  $item->detail['degree_of_education']??'';

            $temp['field_of_Study'] =  $item->detail['field_of_Study']??'';

            $temp['total_average'] =  $item->detail['total_average']??'';

            $temp['en_lang_quality'] =  $item->detail['en_lang_quality']??'';

            $temp['computer_skill'] =  $item->detail['computer_skill']??'';

            $temp['start_work_date'] =  $item->detail['start_work_date']??'';

            $temp['cooperation_type'] =  $item->detail['cooperation_type']??'';

            $temp['description_half_time'] =  $item->detail['description_half_time']??'';

            $temp['rights_requested'] =  $item->detail['rights_requested']??'';

            $temp['busy_now'] =  $item->detail['busy_now']??'';

            $temp['transfer_job'] =  $item->detail['transfer_job']??'';

            $temp['can_guarantee'] =  $item->detail['can_guarantee']??'';

            $temp['guarantee'] =  $item->detail['guarantee']??'';

            $temp['relative_familiarity'] =  $item->detail['relative_familiarity']??'';

            $temp['relative_family_type'] =  $item->detail['relative_family_type']??'';

            $temp['relative_family_name'] =  $item->detail['relative_family_name']??'';

            $temp['relative_family_part'] =  $item->detail['relative_family_part']??'';


            return $temp;
        });
        return $resumes2;

    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return [
            ' نام',
            'موبایل',
            'عنوان شغلی',
            'شماره شناسنامه',
            'محل صدور شناسنامه',
            'کد ملی',
            'دین',
            'تابعیت',
            'سابقه بیمه (ماه)',
            'وضعیت تأهل',
            'جنسیت',
            'آدرس',
            'وضعیت مسکن',
            'تلفن',
            'وضعیت خدمت سربازی',
            'معافیت از خدمت سربازی',
            'نوع معافیت از خدمت سربازی',
            'تاریخ پایان معافیت از خدمت سربازی',
            'محل خدمت سربازی',
            'تاریخ پایان خدمت سربازی',
            'سابقه کاری',
            'مدرک تحصیلی',
            'رشته تحصیلی',
            'معدل کل',
            'تسلط به زبان انگلیسی',
            'مهارت کامپیوتری',
            'تاریخ شروع کار',
            'نوع همکاری',
            'شرح پاره وقت',
            'حقوق درخواستی',
            'آیا در حال حاضر مشغول به کار هستید؟',
            'در صورت انتقال یا ماموریت حاضر به رفتن به شهرستانها و شعبات دیگر هستید؟',
            'آیا می‌توانید ضمانت ارائه بدهید؟',
            'نوع ضمانت:',
            'آیا آشنایی نسبتی با هیچ یک از کارکنان این شرکت دارید؟',
            'نوع نسبت:',
            'نام و نام خانوادگی فرد معرف:',
            'قسمت فرد معرف:',

            ];
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
