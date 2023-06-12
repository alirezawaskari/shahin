@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">ویرایش </h5>
        </div>
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('before_form.update',[ $beforeForm->id])}}">
                @csrf
                @method('PATCH')
                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-3 mb-3">
                        <label>placeholder</label>
                        <input type="text" value="{{ $beforeForm->placeholder }}"
                               class="form-control"
                               name="placeholder"  >
                    </div>

                    <div class="col-12 col-md-3 mb-3">

                        <label>name</label>
                        <input type="text" value="{{ $beforeForm->name }}"
                               class="form-control"
                               name="name" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">

                        <label>value</label>
                        <input type="text" value="{{ $beforeForm->value }}"
                               class="form-control"
                               name="value">
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label>محل نمایش فیلد</label>
                        <select name="data_display_level[]" id="data-display-name" class="form-control select2 " multiple="multiple">
                            @foreach($data_display_levels as $key=>$value)
                             <option {{ in_array($key, $beforeForm->data_display_level) ? 'selected' : '' }} value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label>محل ذخیره فیلد</label>
                        <select name="data_save_level" id="" class="form-control">
                            @foreach($level_save_data as $key => $value)
                                <option {{$beforeForm->data_save_level==$key?'selected':''}} value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-12 mb-12">

                        <label>help</label>
                        <textarea name="help">
                            {{ $beforeForm->help }}
                        </textarea>
                    </div>

                    <div class="col-12 col-md-3 mb-3">

                        <label>class</label>
                        <input type="text" value="{{ $beforeForm->class }}"
                               class="form-control"
                               name="class">
                    </div>

                    <div class="col-12 col-md-3 mb-3">

                        <label>required</label>
                        <select class="form-control" name="required">
                            <option value="0" {{ $beforeForm->required == 0 ? 'selected' : '' }}>غیر اجباری</option>
                            <option value="1" {{ $beforeForm->required == 1 ? 'selected' : '' }}>اجباری</option>
                        </select>
                    </div>


                    <div class="col-12 col-md-3 mb-3">
                        <label>type</label>
                        <select class="form-control" name="type">
                            @foreach(\App\Models\BeforeForm::TYPE as $key => $type)
                                <option value="{{ $key }}" {{ $key == $beforeForm->type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-12">
                        <input type="hidden" value="{{ $beforeForm->beforeformable_id }}"
                               name="beforeformable_id">
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
@endsection
