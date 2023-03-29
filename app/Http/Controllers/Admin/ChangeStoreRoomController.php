<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ChangeStoreRoom;
use App\Models\StoreRoom;
use App\Models\User;
use Illuminate\Http\Request;

class ChangeStoreRoomController extends Controller
{
    public function index(Request $request)
    {
        $change_store_room = ChangeStoreRoom::orderBy('id', 'DESC');

        $store_rooms = StoreRoom::all();

        $queries=[];
        $columns=['type','product_name','name','family','product_type','store_room_id','start_date','end_date'];

        foreach ($columns as $column) {
            if($request->has($column)) {
                if($column === 'name'){
                    $change_store_room->with('user')
                        ->whereHas('user', function ($query) use ($request,$column)  {
                            $query->where('name','like','%'.$request->$column.'%');
                        });
                    $queries[$column] = $request->$column ;
                }

                if($column === 'family'){
                    $change_store_room->with('user')
                        ->whereHas('user', function ($query) use ($request,$column)  {
                            $query->where('family','like','%'.$request->$column.'%');
                        });
                    $queries[$column] = $request->$column ;
                }

                if($column === 'product_name' ){
                    if($request->$column != null){
                        $change_store_room=$change_store_room->with('product')
                            ->whereHas('product', function ($query) use ($request,$column)  {
                                $query->where('name','like','%'.$request->$column.'%');
                            });
                        $queries[$column] = $request->$column ;
                    }
                }

                if($column === 'type') {
                    if ($request->$column != 'all') {
                        $change_store_room = $change_store_room->where('type', $request->$column);
                        $queries[$column] = $request->$column;
                    }
                    if ($request->$column == 'all') {
                        $queries[$column] = 'all';
                    }
                }

                if($column === 'product_type'){
                    if($request->$column != null){
                        $change_store_room=$change_store_room->with('product')
                            ->whereHas('product', function ($query) use ($request,$column)  {
                                $query->where('type',$request->$column);
                            });
                        $queries[$column] = $request->$column ;
                    }
                }

                if($column === 'store_room_id'){
                    if($request->$column !== 'all'){
                        $change_store_room = $change_store_room->where('changestoreroomable_id', $request->$column)
                            ->where('changestoreroomable_type',StoreRoom::class);
                        $queries[$column] = $request->$column ;
                    }
                    if($request->$column === 'all'){
                        $queries[$column] = 'all' ;
                    }
                }

                if($column === 'start_date'){
                    if(trim($request->$column) != ''){
                        $change_store_room = $change_store_room->whereDate('created_at', '>=', $request->$column);
                        $queries[$column] = $request->$column ;
                    }
                }

                if($column === 'end_date'){
                    if(trim($request->$column) != ''){
                        $change_store_room = $change_store_room->whereDate('created_at', '<=', $request->$column);
                        $queries[$column] = $request->$column ;
                    }
                }

            }
        }

        $change_store_room = $change_store_room->paginate(15)->appends($queries);

        if ($request->ajax()) {
            return view('Admin.changeStoreRoom.load', ['change_store_room' => $change_store_room])->render();
        }

        return view('Admin.changeStoreRoom.lists',compact('change_store_room','queries','store_rooms'));
    }
}
