@extends('Admin.master')

@section('content')
    <div class="page-header">
        <div>
            <h3>ویرایش انبار</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ویرایش انبار</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post"
                  action="{{route('storeRoom.update',$storeRoom->id)}}">
                @csrf
                @method('PATCH')

                <div class="row">

                    <div class="col-12 col-md-3 mb-3">
                        <label>کد</label>
                        <input type="text" value="{{ $storeRoom->code }}"
                               class="form-control"
                               name="code" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>نام</label>
                        <input type="text" value="{{ $storeRoom->name }}"
                               class="form-control"
                               name="name" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>انباردار</label>
                        <select class="form-control" name="warehouse_keeper_id">
                            <option value=""></option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                        @if($user->id == $storeRoom->warehouse_keeper_id) selected @endif>{{ $user->name }}  {{ $user->family }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-12 col-md-3 mb-3">
                        <label>نمایندگی</label>
                        <select class="form-control" name="representation_id">
                            <option value="">
                                بدون نمایندگی (دفتر تهران)
                            </option>
                            @foreach($representations as $representation)
                                <option value="{{ $representation->id }}" {{ $storeRoom->admin_representation_id == $representation->id ? 'selected' : '' }}>{{ $representation->fa_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>زیر مجموعه</label>
                        <select class="form-control" name="parent_id">
                            <option value="">
                                لطفا انتخاب کنید..
                            </option>
                            @foreach($store_rooms as $item)
                                <option value="{{ $item->id }}" {{ $storeRoom->parent_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-9 mb-3">
                        <label>آدرس</label>
                        <input type="text" value="{{ $storeRoom->address }}"
                               class="form-control"
                               name="address" required>
                    </div>

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ویرایش">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
