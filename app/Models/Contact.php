<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string[]
     */
    protected $casts = [
        'files' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responder()
   {
      return $this->belongsTo(User::class, 'responder_id');
   }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $name = request('name');
        $text = request('text');
        $card = request('card');
        $status = request('status');
        if (auth()->user()->admin_representation) {
            $query->where('representation_id', auth()->user()->admin_representation->id);
        }
        if(isset($name) && $name != '')
        {
            $query->where('name', 'LIKE', '%' . $name . '%');
        }

        if(isset($text) && $text != '')
        {
            $query->where('text', 'LIKE', '%' . $text . '%');
        }

        if(isset($card) && $card != 'all')
        {
            $query->where('card_id', $card);
        }
        if(isset($status) && $status != 'all')
        {
            $query->where('status', $status);
        }

        return $query;
    }
}
