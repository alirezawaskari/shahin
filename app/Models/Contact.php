<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
   protected $guarded = [];

    protected $casts = [
        'files' => 'array',
    ];

   public function card()
   {
      return $this->belongsTo(Card::class, 'card_id');
   }

   public function responder()
   {
      return $this->belongsTo(User::class, 'responder_id');
   }

    public function scopeFilter($query)
    {
        $name = request('name');
        $text = request('text');
        $card = request('card');
        $status = request('status');

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
