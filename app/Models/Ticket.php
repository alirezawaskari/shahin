<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string[]
     */
    protected $casts = [
          'file' => 'array',
     ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
     {
          return $this->belongsToMany(User::class);
     }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
     {
          return $this->belongsTo(User::class, 'user_id');
     }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
     {
          return $this->hasMany(TicketAnswer::class)->orderBy('id', 'ASC');
     }

    /**
     * @param $query
     * @return mixed
     */
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

    /**
     * @param $ticket
     * @return string
     */
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
