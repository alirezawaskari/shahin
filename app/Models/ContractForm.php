<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractForm extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    const TYPE = [
        'number' => 'فیلد عددی',
        'text' => 'فیلد متنی',
        'select' => 'لیست کشویی',
        'textarea ' => 'متن بلند',
        'multi-select' => 'چند انتخابی',
        'file' => 'فایل',
        'other_document_image' => 'سایر مدارک',
        'date' => 'تاریخ',
    ];

    const VALUE = [
        'check' => 'چک',
        'certificate' => 'گواهی کسر از حقوق',
        'promissorynote' => 'سفته',
        'all' => 'مشترک'
    ];

    const LEVEL = [
        'basic_documents' => 'مدارک اولیه',
        'original_documents' => 'مدارک اصلی',
        'guaranteed_delivery_image' => 'تصویر تحویل ضمانت',
        'job' => 'شغل',
        'request_contract_settlement' => 'درخواست تسویه حساب',
        'description_request_contract_settlement' => 'توضیحات تسویه حساب',
    ];

    public function beforeformitems()
    {
        return $this->morphMany(BeforeFormItem::class, 'beforeformitemable');
    }
}
