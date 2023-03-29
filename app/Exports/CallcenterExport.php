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

class CallcenterExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    use Exportable;
    private $tasks;

    /**
     * CallcenterExport constructor.
     * @param $tasks
     */
    public function __construct($tasks)
    {
        $this->tasks = $tasks;
    }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $this->tasks = $this->tasks->get();
        $map_tasks = $this->tasks->map(function ($item) {
            $temp = [];
            switch ($item->type) {
                case 0:
                    $temp['type'] = 'ثبت نام';
                    break;
                case 1:
                    $temp['type'] = 'پیامک';
                    break;
                default:
                    $temp['type'] = 'ثبت نام تماس ورودی';
            }
            $temp['sms'] = sms::find($item->sms_id)->sender_number ?? '';
            $temp['reg'] = User::find($item->reg_id)->phone ?? '';
            $temp['callcneter'] = User::find($item->user_id)->family ?? 'سایت';
            $temp['service'] = $item->type == 1 ? $this->setService($item->sms->sender_number , $item) : $this->setService($item->register->phone , $item);
            $temp['price'] = $item->type == 1 ? $this->setPrice($item->sms->sender_number , $item) : $this->setPrice($item->register->phone , $item);

            switch ($item->status) {
                case 0:
                    $temp['status'] = 'تایید نشده';
                    break;
                case 1:
                    $temp['status'] = 'در حال بررسی';
                    break;
                case 2:
                    $temp['status'] = 'نیاز به تماس مجدد';
                    break;
                default:
                    $temp['status'] = 'تایید نشده';
            }

            $temp['description'] = $item->description;
            $temp['created_at'] = verta($item->created_at)->format('%d %B ، %Y - H:i:s');

            return $temp;
        });
        return $map_tasks;
    }

    public function setService($phone , $item)
    {
        $user = User::wherePhone($phone)->first();
        if ($user){
            if($user->service_id == 100)
            {
                if ($item->factors->first()){
                    if($user->created_at < $item->factors->first()->created_at) {
                        $temp = [];
                        foreach ($item->factors->first()->factorItems as $item) {
                            if (isset($item->factoritemable->title))
                             array_push($temp, $item->factoritemable->title);
                        }
                        return implode(',', $temp);
                    }
                }
                return 'دریافت مجوز کارگزاری';
            }
                if ($item->factors->first()){
                    $temp = [];
                    foreach ($item->factors as $factor){
                        foreach ($factor->factorItems as $item) {
                            if (isset($item->factoritemable->title))
                             array_push($temp, $item->factoritemable->title);
                        }
                    }
                    return implode(',' , $temp);
                }
                if($user->service)
                {
                   return $user->service->title;
                }
        }
        else{
            return 'ندارد';
        }
    }
	public function setPrice($phone , $item)
    {
        $user = User::wherePhone($phone)->first();
        if ($user){
            if($user->service_id == 100)
            {
                if ($item->factors->first()){
                    if($user->created_at < $item->factors->first()->created_at) {
                        $temp = $item->factors->first();
                        $temp = $temp->price;
                        return $temp;
                    }
                }
                return 'دریافت مجوز کارگزاری';
            }
                if ($item->factors->first()){
                    $temp = [];
                    foreach ($item->factors as $factor){
                            array_push($temp, $factor->price);
                    }
                    return implode(',' , $temp);
                }
                if($user->service)
                {
                   return $user->service->price;
                }
        }
        else{
            return 'ندارد';
        }
    }
    public function headings(): array
    {
        return ['نوع' , 'شماره پیامک شده' , 'شماره کاربر ثبت نامی' ,'کالسنتر' ,'نوع خدمت','مبلغ' ,'وضعیت','توضیحات','تاریخ یادآوری','زمان یادآوری','تاریخ ثبت'];
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
