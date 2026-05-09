<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractView extends Model {
    protected $fillable = [
        'contract',
        'contract_type',
        'contract_id'
    ];
    /**
     * Summary of create
     * @param $item
     * @param mixed $content
     * @return void
     */
    public static function store( $item  , $content) {

        ContractView::create( [
            'contract' => $content,
            'contract_type' => TotalFactorItem::class,
            'contract_id' => $item->id
        ] );
    }
}
