<?php

namespace App\Exports;

use App\Models\Phone;
use App\Models\Product;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use PhpParser\Node\Stmt\Return_;

class PhonesExport implements FromQuery
{
    use Exportable;

    public function query()
    {
        $query = Phone::query();
        if (request('type') != 'all') {
            $query->where('phone_list_id' , "0")->whereBetween('created_at', [Carbon::createFromTimestamp(request('start_time') / 1000)->toDateString(), Carbon::createFromTimestamp(request('end_time') / 1000)->toDateString()]);
        }

        return $query;
    }
}
