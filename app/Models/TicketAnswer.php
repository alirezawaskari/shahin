<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAnswer extends Model
{
   protected $guarded = [];

   protected $casts = [
     'file' => 'array',
   ];

   public function ticket()
   {
        return $this->belongsTo(Ticket::class);
   }

   public function user()
   {
        return $this->belongsTo(User::class);
   }
}
