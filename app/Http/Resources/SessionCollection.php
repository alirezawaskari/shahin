<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Session;

class SessionCollection extends ResourceCollection
{

    /**
     * @var
     */
    public $sessions;

    /**
     * @param $sessions
     * @param $collection
     */
    public function __construct($sessions , $collection)
    {
        parent::__construct($collection);
        $this->sessions = $sessions;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'sort_by' => "date",
                    'id' => $item->id,
                    'user_id' => isset($item->factorItem) && isset($item->factorItem->factor) ? $item->factorItem->factor->user->id : null,
                    'name' =>  isset($item->factorItem) && isset($item->factorItem->factor) ? ($item->factorItem->factor->user->name.' '.$item->factorItem->factor->user->family) : 'ندارد',
                    'phone' =>  isset($item->factorItem) && isset($item->factorItem->factor) ? $item->factorItem->factor->user->phone : 'ندارد',
                    'discription' => $this->setDiscription($item),
                    'duration' => $item->duration,
                    'user' => $item->user->family,
                    'confirmer' => $item->confirmer ? $item->confirmer->family : 'ندارد',
                    'date' => verta($item->date)->format('%d %B ، %Y - H:i'),
                    'status' => $this->setStatus($item->status),
                    'factor_item' => $item->factorItem ? $item->factorItem->resnumber : '',
                    'histories' => isset($item->factorItem->factor->user)? Helper::userHistories($item->factorItem->factor->user):[],
                    'entry_start' =>isset($item->entries()->first()->start)?verta($item->entries()->first()->start)->format('%d %B ، %Y - H:i'):null,
                    'entry_end' =>isset($item->entries()->first()->end)?verta($item->entries()->first()->end)->format('%d %B ، %Y - H:i'):null,
                ];
            }),
            'meta' =>  [
                'sum_price' => number_format($this->collection->sum('price')),
				'total_price' => number_format($this->sessions->sum('price')),
            ]
        ];
    }
    /**
     * @param Session $item
     * @return string
     */
    public function setDiscription(Session $item)
    {
        if(auth()->user()->isAbleTo('session-detail'))
        {
            return $item->description_before .(isset($item->agent) ? '('.$item->agent->name.' '.$item->agent->family.')' : '');
        }
        elseif(auth()->user()->isAbleTo('session'))
        {
            return $item->description_before;
        }
    }

    /**
     * @param $item
     * @return string
     */
    public function setStatus($item)
    {
        return Session::status($item);
    }
}
