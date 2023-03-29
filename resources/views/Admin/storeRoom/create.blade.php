@extends('Admin.master')

@section('content')
<div class="page-header">
    <div>
        <h3>انبار جدید</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">انبار جدید</li>
            </ol>
        </nav>
    </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('storeRoom.store')}}">
                @csrf

                <div class="row">

                    <div class="col-12 col-md-4 mb-3">
                        <label>کد</label>
                        <input type="text" value="{{ old('code') }}"
                               class="form-control" title="code" name="code" required>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>نام</label>
                        <input type="text" value="{{ old('name') }}"
                               class="form-control" title="name" name="name" required>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>انبارداران</label>
                        <select class="form-control" name="warehouse_keeper_id" title="">
                            <option value=""></option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}  {{ $user->family }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-12 col-md-4 mb-3">
                        <label>نمایندگی</label>
                        <select class="form-control" name="representation_id" title="representation_id">
                            <option value="">
                                بدون نمایندگی (دفتر تهران)
                            </option>
                            @foreach($representations as $representation)
                                <option value="{{ $representation->id }}">{{ $representation->fa_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>زیر مجموعه</label>
                        <select class="form-control" name="parent_id" title="parent_id">
                            <option value="">
                               لطفا انتخاب کنید..
                            </option>
                            @foreach($store_rooms as $store_room)
                                <option value="{{ $store_room->id }}">{{ $store_room->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-8 mb-3">
                        <label>آدرس</label>
                        <input type="text" value="{{ old('address') }}"
                               class="form-control" title="address" name="address" required>
                    </div>

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
