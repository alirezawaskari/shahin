@extends('Admin.master')

@section('content')
    <div class="page-header">
        <div>
            <h3>فهرست انبار های زیر مجموعه</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> انبار</li>
                    @if(isset($parent_name))<li class="breadcrumb-item active" aria-current="page"> انبار های زیر مجموعه {{$parent_name}}</li> @endif
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-primary dropdown-toggle">اقدامات</button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{route('storeRoom.create')}}" class="dropdown-item">ایجاد انبار جدید </a>
            </div>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>کد</th>
                    <th>نام</th>
                    <th>نمایندگی</th>
                    <th>انباردار</th>
                    <th>والد</th>
                    <th>آدرس</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($store_rooms as $key => $store_room)
                    <tr class="active" id="item_{{$store_room->id}}" style="cursor: move;">
                        <td>{{ $store_room->code }}</td>
                        <td>{{ $store_room->name }}</td>
                        <td>{{ $store_room->representation ? $store_room->representation()->withTrashed()->first()->fa_name : 'م تهران' }}</td>
                        <td>{{ \App\Helpers\Helper::user($store_room->user_id,'name') }}  {{ \App\Helpers\Helper::user($store_room->user_id,'family') }}</td>
                        <td>
                            @if($store_room->parent_id != null)
                                {{ \App\Models\StoreRoom::getStoreRoom($store_room->parent_id,'name') }}
                            @else
                                ---
                            @endif
                        </td>
                        <td>{{ $store_room->address }}</td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="/dashboard/storeRoom/{{$store_room->id}}/edit"><button type="button" class="dropdown-item pointer">ویرایش</button></a>
                                    <a href="/dashboard/storeRoomGood/{{$store_room->id}}"><button type="button" class="dropdown-item pointer">لیست کالا</button></a>
                                    <a href="/dashboard/storeRoomMaterial/{{$store_room->id}}"><button type="button" class="dropdown-item pointer">لیست مواد اولیه</button></a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$store_rooms->links()}}
        </div>
    </div>
@stop

@section('js')

@endsection