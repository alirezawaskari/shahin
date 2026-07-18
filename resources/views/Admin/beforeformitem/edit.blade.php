@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('before_form_item.update', $beforeFormItem)}}">
                @csrf
                @method('PATCH')
                @include('Admin.layouts.errors')
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label>title</label>
                        <input type="text" value="{{ $beforeFormItem->title }}"
                               class="form-control"
                               name="title" required>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>value</label>
                        <input type="text" value="{{ $beforeFormItem->value }}"
                               class="form-control"
                               name="value" required>
                    </div>
                    <div class="col-12">
{{--                        <input type="hidden" value="{{ $beforeFormItem->before_form_id }}"--}}
{{--                               name="before_form_id">--}}
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
