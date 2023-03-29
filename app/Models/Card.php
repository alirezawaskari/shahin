<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $guarded = [];
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function factor_item()
    {
        return $this->belongsTo(FactorItem::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class , 'ticketable_id');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public static function getCard($id)
    {
        $card=Card::find($id);return $card->title;
    }
}
