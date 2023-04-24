<?php

namespace App\Exports;

use App\Models\Phone;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;

class PhonesExport implements FromQuery
{
    use Exportable;

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        $query = Phone::query();
        if (request('type') != 'all') {
            $query->where('phone_list_id' , "0")->whereBetween('created_at', [Carbon::createFromTimestamp(request('start_time') / 1000)->toDateString(), Carbon::createFromTimestamp(request('end_time') / 1000)->toDateString()]);
        }

        return $query;
    }
}
