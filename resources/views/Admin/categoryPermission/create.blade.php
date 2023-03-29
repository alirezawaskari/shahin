@extends('Admin.master')

@section('content')
<form class="form-horizontal" role="form" method="post" action="{{route('category-permission.store')}}"
    enctype="multipart/form-data">
    @csrf
    <div class="col-md-3">
        @include('Admin.layouts.errors')
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="form-row">
                    <div class="col-12 col-md-3">
                        <label for="name">عنوان دسته بندی </label>
                        <input type="text" name="name" class="form-control" value="">
                    </div>
                    <div class="col-12 col-md-12">
                        <br>
                        <button type="submit" class="btn btn-success">ثبت </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

