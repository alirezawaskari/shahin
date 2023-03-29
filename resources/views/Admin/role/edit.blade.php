@extends('Admin.master')

@section('content')
<div class="card">
    <div class="card-body">
        <form class="form-horizontal" role="form" method="post" action="{{route('role.update' , [$role->id])}}"
            enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            @include('Admin.layouts.errors')
            <div class="row">
                <div class="col-12 col-md-4 mb-3">
                    <label>نام</label>
                    <input type="text" value="{{ $role->display_name }}" class="form-control" name="display_name"
                        required>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>کلمه کلیدی</label>
                    <input type="text" value="{{ $role->name }}" class="form-control" name="name" required>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>توضیح نقش</label>
                    <input type="text" value="{{ $role->description }}" class="form-control" name="description"
                        required>
                </div>

                <div class="col-12">
                    <h4 class="mb-3 mt-5"><i class="fa fa-lock" aria-hidden="true"></i> دسترسی ها (دسترسی های مورد نیاز
                        نقش مربوطه را از فهرست زیر انتخاب کنید.)</h4>
                    @foreach(\App\Models\CategoryPermission::get() as $cat)
                    <div class="col-12 mb-3 border border-gray p-4">
                        <h5 class="border-bottom pb-2 mb-4 alert alert-primary text-center">{{ $cat->name }}</h5>
                        <div class="row">
                            @foreach(\App\Models\Permission::get()->where('cat_id', $cat->id) as $per)
                            <div class="col-12 col-md-2 mb-3">
                                <input type="checkbox" name="permission_id[]" value="{{$per->id}}" {{
                                    in_array(trim($per->id) , $role->permissions->pluck('id')->toArray()) ? 'checked' :
                                '' }}> {{ $per->display_name }} </input>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="col-12">
                    <input type="submit" class="btn btn-success border-0" value="ثبت">
                </div>

            </div>
        </form>
    </div>
</div>
@stop
