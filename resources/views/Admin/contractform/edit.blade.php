@extends('Admin.master')

@section('content')
<div class="page-header">
    <div>
        <h3>ویرایش فیلد</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="{{ route('contractForm.index') }}">مدارک قرارداد</a></li>
                <li class="breadcrumb-item active" aria-current="page">ویرایش فیلد</li>
            </ol>
        </nav>
    </div>
</div>
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('contractForm.update' , [ $contractForm->id])}}">
                @csrf
                @method('PATCH')

                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-3 mb-3">

                        <label>نام (عنوان انگلیسی)</label>
                        <input type="text" value="{{$contractForm->name}}"
                               class="form-control"
                               name="name" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>متن داخل فیلد(placeholder)</label>
                        <input type="text" value="{{$contractForm->placeholder}}"
                              class="form-control"
                               name="placeholder" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">

                        <label>توضیحات فیلد</label>
                        <input type="text" value="{{$contractForm->help}}"
                               class="form-control"
                               name="help" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">

                        <label>الزامی بودن فیلد</label>
                        <select class="form-control" name="required">
                            <option value="0" {{$contractForm->required ? '' : 'selected'}}>غیر اجباری</option>
                            <option value="1" {{$contractForm->required ? 'selected' : ''}}>اجباری</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>نوع فیلد</label>
                        <select class="form-control" name="type">
                            @foreach($contractForm::TYPE as $key => $type)
                                <option value="{{ $key }}" {{ $contractForm->type == $key ? 'selected' : ''}}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>نوع ضمانت</label>
                        <select class="form-control" name="value">
                            @foreach($contractForm::VALUE as $key => $value)
                                <option value="{{ $key }}" {{ $contractForm->value == $key ? 'selected' : ''}}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>نوع دسته بندی</label>
                        <select class="form-control" name="level">
                            @foreach($contractForm::LEVEL as $key => $level)
                                <option value="{{ $key }}" {{ $contractForm->level == $key ? 'selected' : ''}}>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
