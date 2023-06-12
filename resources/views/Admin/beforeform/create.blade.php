@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">ایجاد  </h5>
        </div>
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('before_form.store')}}">
                @csrf
                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-3 mb-3">
                        <label>placeholder</label>
                        <input type="text" value="{{old('placeholder')}}"
                               class="form-control"
                               name="placeholder" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">

                        <label>name</label>
                        <input type="text" value="{{old('name')}}"
                               class="form-control"
                               name="name" required>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label>محل نمایش فیلد</label>
                        <select name="data_display_level[]" id="data-display-name" class="form-control" multiple="multiple">
                            @foreach($data_display_levels as $key=>$value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-12 col-md-3 mb-3">

                        <label>محل ذخیره فیلد</label>
                        <select name="data_save_level" id="" class="form-control">
                            @foreach($level_save_data as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-12 mb-12">

                        <label>help</label>
                        <textarea required name="help">
                            {{old('help')}}
                        </textarea>
{{--                        <input type="text" value="{{old('help')}}"--}}
{{--                               class="form-control"--}}
{{--                               name="help" required>--}}
                    </div>

                    <div class="col-12 col-md-3 mb-3">

                        <label>class</label>
                        <input type="text" value="{{old('class')}}"
                               class="form-control"
                               name="class" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>required</label>
                        <select class="form-control" name="required">
                            <option value="0">غیر اجباری</option>
                            <option value="1">اجباری</option>
                        </select>
                    </div>


                    <div class="col-12 col-md-3 mb-3">
                        <label>required</label>
                        <select class="form-control" name="type">
                            @foreach(\App\Models\BeforeForm::TYPE as $key => $type)
                                <option value="{{ $key }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-12">
                        <input type="hidden" value="{{ $before_formable->id }}"
                               name="beforeformable_id">
                        <input type="hidden" value="{{  get_class($before_formable) }}"
                               name="beforeformable_type">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
@section('js')
    <script src="{{asset('assets/script/ckeditor/ckeditor.js')}}"></script>
    <script>
        CKEDITOR.replace('help',ck_uploader)
    </script>
    <script>
        $(document).ready(function () {
            $('#data-display-name').select2({
                placeholder: "انتخاب وضعیت"
            });
        })
    </script>
@endsection
