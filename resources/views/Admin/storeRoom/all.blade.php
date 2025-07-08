@extends('Admin.master')

@section('content')
<div class="page-header">
    <div>
        <h3>فهرست انبار</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست انبار</li>
            </ol>
        </nav>
    </div>
    @include('Admin.storeRoom.layouts')
</div>
<div class="card position-relative">
    <div class="card-body">
        <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
                <thead>
                    <tr>
                        <th>کد</th>
                        <th>نام</th>
                        <th>نمایندگی</th>
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
                        <td>{{ $store_room->address }}</td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{ route('storeRoom.edit' , [$store_room->id]) }}"><button type="button" class="dropdown-item pointer">ویرایش</button></a>
                                    <a href="{{ url('dashboard/create-product-storeRoom' , [$store_room->id]) }}"><button type="button" class="dropdown-item pointer">کالا ها</button></a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @include('Admin.layouts.pagination')
    </div>
</div>
@stop

@section('js')
<script>
    @if(session() -> get('store_room_delete') == 'success')
    toastr.success('با موفقیت حذف شد!');
    @elseif(session() -> get('store_room_create') == 'success')
    toastr.success('با موفقیت ایجاد شد!');
    @elseif(session() -> get('store_room_update') == 'success')
    toastr.success('با موفقیت آپدیت شد!');
    @endif
</script>
<script src="/assets/dashboard/js/pagination.js"></script>
@endsection
