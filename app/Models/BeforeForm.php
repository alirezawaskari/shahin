<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeforeForm extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $casts = [
        'data_display_level' => 'array',
    ];

    /**
     *
     */
    const TYPE = [
        'number' => 'فیلد عددی',
        'text' => 'فیلد متنی',
        'select' => 'لیست کشویی',
        'textarea' => 'متن بلند',
        'multi-select' => 'چند انتخابی',
        'file' => 'فایل',
        'checkbox' => 'انتخابی',
        'radio' => 'تک انتخابی',
        'factor_dentist' => 'فاکتور خدمات دندانپزشکی',
        'factor' => 'فاکتور عمومی کالا',
        'multi_file' => 'چند فایلی',
        'confirm' => 'تائیدیه',
        'rules' => 'مفاد قرارداد',
        'total-price' => 'جمع کل فاکتور',
        'factor-image' => 'تصویر فاکتور',
        'factor_building' => 'فاکتور خدمات ساختمانی',
        'factor_legal' => 'فاکتور خدمات حقوقی',
        'factor_service' => 'فاکتور عمومی خدمات',
        'factor_skin_hair' => 'فاکتور زیبایی پوست و مو',
        'excel' => 'فایل اکسل',
        'image' => 'تصویر',
        'calendar' => 'تقویم',
    ];


    /**
     *
     */
    const DATA_DISPLAY_LEVEL = [
        'site' => 'بارگذاری مدارک پرونده - سایت',
        'show_factor_site' => 'مشاهده پرونده- فاکتور - سایت',
        'panel_user_info' => ' اطلاعات مرتبط با کاربر - امور اجرایی',
        'panel_factor' => 'ویرایش فاکتور - امور اجرایی',
        'panel_factor_create' => 'ایجاد فاکتور - امور اجرایی',
        'panel_upload_before_work_doc' => 'مدارک مورد نیاز قبل کار - امور اجرایی',
        'panel_upload_after_work_doc' => 'مدارک مورد نیاز بعد کار - امور اجرایی',
        'panel_complation_file_work' => 'اتمام کار - امور اجرایی',
        'panel_print_form' => 'پرینت فرم ثبت نام - امور اجرایی',
        'panel_show_file' => 'فاکتور - امور اداری و مالی',
        'panel_basic_document' => 'مدارک اولیه و پشتیبانی - امور اداری و مالی',
        'panel_determine_center' => 'ثبت مشاوره و ارجاع به مرکز - امور اداری و مالی',
        'show_recommendations_site' => 'مشاهده پرونده - توصیه ها و دستورات - سایت ',
    ];
    /**
     *
     */
    const LEVEL_SAVE_DATA = [
        'user' => 'ذخیره در جدول کاربران',
        'factor_item' => 'ذخیره در جدول پرونده ها در فیلدهای تعریف شده',
        'factor_item_factor_values' => 'ذخیره در فیلد factor_values در جدول پرونده ها',
        'null' => 'درجایی ذخیره نمی شود'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function beforeformable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function beforeformitems()
    {
        return $this->morphMany(BeforeFormItem::class, 'beforeformitemable');
    }

    /**
     * @param null $data_save_level
     * @return string|string[]
     */
    public static function getLevelSaveData($data_save_level=null)
    {
        if (!is_null($data_save_level)){
            return self::LEVEL_SAVE_DATA[$data_save_level];
        }else{
            return self::LEVEL_SAVE_DATA;
        }
    }

    /**
     * @param null $data_display_level
     * @return string|string[]
     */
    public static function getDataDisplayLevel($data_display_level=null)
    {
        if (!is_null($data_display_level)){
            return self::DATA_DISPLAY_LEVEL[$data_display_level];
        }else{
            return self::DATA_DISPLAY_LEVEL;
        }

    }
}
