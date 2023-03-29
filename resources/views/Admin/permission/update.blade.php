@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('permission.update',$permission->id)}}">
                @csrf
                @method('PUT')
                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-6 mb-3">
                        <label>نام دسترسی</label>
                        <input type="text" value="{{ $permission->display_name }}"
                               class="form-control"
                               name="display_name" required>
                    </div>


                    <div class="col-12 col-md-6 mb-3">
                        <label>کلمه کلیدی دسترسی</label>
                        <label class="form-control"> {{$permission->name}}</label>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>توضیح دسترسی</label>
                        <input type="text" value="{{ $permission->description }}"
                               class="form-control"
                               name="description" required>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>دسته بندی</label>

                        <select name="category_id" id="" class="form-control">
                            <option>
                                دسته بندی دسترسی را انتخاب کنید
                            </option>
                            @foreach($permission_categories as $category)
                            <option value="{{$category->id}}" {{$category->id==$permission->cat_id?'selected':''}}>
                                {{$category->name}}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
