<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractForm extends Model
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
     *list of type for contractForm
     */
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

    /**
     *list of value
     */
    const VALUE = [
        'check' => 'چک',
        'certificate' => 'گواهی کسر از حقوق',
        'promissorynote' => 'سفته',
        'all' => 'مشترک'
    ];

    /**
     *list levels of contractForm
     */
    const LEVEL = [
        'basic_documents' => 'مدارک اولیه',
        'original_documents' => 'مدارک اصلی',
        'guaranteed_delivery_image' => 'تصویر تحویل ضمانت',
        'job' => 'شغل',
        'request_contract_settlement' => 'درخواست تسویه حساب',
        'description_request_contract_settlement' => 'توضیحات تسویه حساب',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function beforeformitems()
    {
        return $this->morphMany(BeforeFormItem::class, 'beforeformitemable');
    }
}
