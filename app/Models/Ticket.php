<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
     protected $guarded = [];

     protected $casts = [
          'file' => 'array',
     ];

     public function users()
     {
          return $this->belongsToMany(User::class);
     }

     public function user()
     {
          return $this->belongsTo(User::class, 'user_id');
     }

     public function answers()
     {
          return $this->hasMany(TicketAnswer::class)->orderBy('id', 'ASC');
     }

     public function scopeFilter($query)
     {
          $title = request('title');
          $user = request('user');

          if(isset($user) && $user != 'all')
          {
             $query->where('user_id', $user);
          }
          if(isset($title) && $title != '')
          {
            $query->where('title', 'LIKE', '%' . $title . '%');
          }
          return $query;
     }

     public static function setReceiver($ticket)
     {
          $temp = [];
          $receivers = $ticket->users;
          foreach ($receivers as $receiver) {
               if (isset($receiver)) {
                    array_push($temp, $receiver->family);
               }
          }
          return implode(' - ', $temp);
     }
}
